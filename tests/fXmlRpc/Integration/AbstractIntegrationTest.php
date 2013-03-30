<?php
/**
 * Copyright (C) 2012-2013
 * Lars Strojny, InterNations GmbH <lars.strojny@internations.org>
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN THE
 * SOFTWARE.
 */

namespace fXmlPRC\Integration;

use fXmlRpc;
use Exception;

abstract class AbstractIntegrationTest extends AbstractCombinatoricsClientTest
{
    protected static $command;

    protected static $server;

    protected static $pipes;

    protected static $restartServerInterval = 0;

    private static $runCount = 0;

    protected static function startServer()
    {
        self::$server = proc_open(
            static::$command,
            array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'w'),
            ),
            self::$pipes,
            __DIR__ . '/Fixtures'
        );
    }

    protected static function stopServer()
    {
        foreach (self::$pipes as $pipe) {
            fclose($pipe);
        }

        proc_terminate(self::$server);
        proc_close(self::$server);
    }

    public static function setUpBeforeClass()
    {
        static::startServer();

        if (!is_resource(self::$server)) {
            throw new \Exception(
                'Could not start server' . PHP_EOL
                . fread(static::$pipes[1], 65536) . PHP_EOL
                . fread(static::$pipes[2], 65536)
            );
        }

        sleep(2);
    }

    public static function tearDownAfterClass()
    {
        static::stopServer();
        sleep(1);
    }

    public function setUp()
    {
        if (static::$restartServerInterval === 0) {
            return;
        }

        if (++self::$runCount !== static::$restartServerInterval) {
            return;
        }

        self::$runCount = 0;
        static::tearDownAfterClass();
        static::setUpBeforeClass();
    }

    /**
     * @dataProvider getClients
     */
    public function testNil($client)
    {
        $result = null;
        $this->assertSame($result, $client->call('system.echoNull', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testArray($client)
    {
        $result = range(0, 10);
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testStruct($client)
    {
        $result = array('FOO' => 'BAR', 'BAZ' => 'BLA');
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testString($client)
    {
        $result = 'HELLO WORLD <> & ÜÖÄ';
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testBase64($client)
    {
        $expected = fXmlRpc\Value\Base64::serialize('HELLO WORLD');
        $result = $client->call('system.echo', array($expected));
        $this->assertSame($expected->getEncoded(), $result->getEncoded());
        $this->assertSame($expected->getDecoded(), $result->getDecoded());
    }

    /**
     * @dataProvider getClients
     */
    public function testInteger($client)
    {
        $result = 100;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testNegativeInteger($client)
    {
        $result = -100;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testFloat($client)
    {
        $result = 100.12;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testNegativeFloat($client)
    {
        $result = -100.12;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testDate($client)
    {
        $result = new \DateTime('2011-01-12 23:12:10', new \DateTimeZone('UTC'));
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testComplexStruct($client)
    {
        $result = array(
            'el1' => array('one', 'two', 'three'),
            'el2' => array('first' => 'one', 'second' => 'two', 'third' => 'three'),
            'el3' => range(1, 100),
            'el4' => array(
                new \DateTime('2011-02-03 20:11:15', new \DateTimeZone('UTC')),
                new \DateTime('2011-02-03 20:11:15', new \DateTimeZone('UTC')),
            ),
            'el5' => 'str',
            'el6' => 1234,
            'el7' => -1234,
            'el8' => 1234.12434,
            'el9' => -1234.3245023,
        );
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testFault($client)
    {
        try {
            $client->call('system.fault');
            $this->fail('Expected exception');
        } catch (fXmlRpc\Exception\ResponseException $e) {
            $this->assertContains('ERROR', $e->getMessage());
            $this->assertContains('ERROR', $e->getFaultString());
            $this->assertSame(0, $e->getCode());
            $this->assertSame(123, $e->getFaultCode());
        }
    }

    /**
     * @dataProvider getClients
     */
    public function testServerReturnsInvalidResult($client)
    {
        $client->setUri($this->errorEndpoint);

        try {
            $client->call('system.failure');
            $this->fail('Exception expected');
        } catch (\fXmlRpc\Exception\HttpException $e) {
            $this->assertInstanceOf('fXmlRpc\Exception\TransportException', $e);
            $this->assertInstanceOf('fXmlRpc\Exception\ExceptionInterface', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('An HTTP error occurred', $e->getMessage());
            $this->assertSame(500, $e->getCode());
        }
    }

    /**
     * @dataProvider getClients
     */
    public function testServerNotReachableViaTcpIp($client)
    {
        $client->setUri('http://localhost:23124/');

        try {
            $client->call('system.failure');
            $this->fail('Exception expected');
        } catch (\fXmlRpc\Exception\TcpException $e) {
            $this->assertInstanceOf('fXmlRpc\Exception\TransportException', $e);
            $this->assertInstanceOf('fXmlRpc\Exception\ExceptionInterface', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('A transport error occurred', $e->getMessage());
            $this->assertSame(0, $e->getCode());
        }
    }

}
