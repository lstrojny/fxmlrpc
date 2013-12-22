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
use fXmlRpc\ClientInterface;
use hmmmath\Fibonacci\FibonacciFactory;
use Symfony\Component\Process\Process;

abstract class AbstractIntegrationTest extends AbstractCombinatoricsClientTest
{
    /** @var string */
    protected static $command;

    /** @var Process */
    protected static $server;

    protected static $restartServerInterval = 0;

    protected static $errorEndpoint;

    private static $runCount = 0;

    protected static function startServer()
    {
        self::$server = new Process(static::$command, __DIR__ . '/Fixtures');
        self::$server->start();
        static::pollWait();
    }

    protected static function stopServer()
    {
        self::$server->stop();
    }

    public static function setUpBeforeClass()
    {
        static::startServer();
    }

    private static function pollWait()
    {
        $parts = parse_url(static::$endpoint);
        foreach (FibonacciFactory::sequence(50000, 10000) as $offset => $sleepTime) {
            usleep($sleepTime);

            $socket = @fsockopen($parts['host'], $parts['port'], $errorNumber, $errorString, 0.5);
            if ($socket !== false) {
                fclose($socket);
                break;
            }

            if ($offset > 5) {
                static::startServer();
                break;
            }
        }
    }

    public static function tearDownAfterClass()
    {
        static::stopServer();
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
        static::stopServer();
        static::startServer();
    }

    /**
     * @dataProvider getClients
     */
    public function testNil(ClientInterface $client)
    {
        $result = null;
        $this->assertSame($result, $client->call('system.echoNull', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testArray(ClientInterface $client)
    {
        $result = range(0, 10);
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testStruct(ClientInterface $client)
    {
        $result = array('FOO' => 'BAR', 'BAZ' => 'BLA');
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testString(ClientInterface $client)
    {
        $result = 'HELLO WORLD <> & ÜÖÄ';
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testBase64(ClientInterface $client)
    {
        $expected = fXmlRpc\Value\Base64::serialize('HELLO WORLD');
        $result = $client->call('system.echo', array($expected));
        $this->assertSame($expected->getEncoded(), $result->getEncoded());
        $this->assertSame($expected->getDecoded(), $result->getDecoded());
    }

    /**
     * @dataProvider getClients
     */
    public function testInteger(ClientInterface $client)
    {
        $result = 100;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testNegativeInteger(ClientInterface $client)
    {
        $result = -100;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testFloat(ClientInterface $client)
    {
        $result = 100.12;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testNegativeFloat(ClientInterface $client)
    {
        $result = -100.12;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testDate(ClientInterface $client)
    {
        $result = new \DateTime('2011-01-12 23:12:10', new \DateTimeZone('UTC'));
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testComplexStruct(ClientInterface $client)
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
    public function testFault(ClientInterface $client)
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
    public function testServerReturnsInvalidResult(ClientInterface $client)
    {
        $client->setUri(static::$errorEndpoint);

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
}
