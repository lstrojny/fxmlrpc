<?php
/**
 * Copyright (C) 2012
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

namespace FXMLPRC\Integration;

use FXMLRPC;
use Exception;

class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    private $clients = array();

    private $dependencies = array();

    private $pos = 0;

    private static $server;

    private static $pipes;

    public static function setUpBeforeClass()
    {
        $serverCommand = 'node ' . escapeshellarg('server.js');
        self::$server = proc_open(
            $serverCommand,
            array(
                0 => array('pipe', 'r'),
                1 => array('pipe', 'w'),
                2 => array('pipe', 'r'),
            ),
            self::$pipes,
            __DIR__ . '/Fixtures'
        );
        sleep(2);
    }

    public static function tearDownAfterClass()
    {
        proc_terminate(self::$server);

        foreach (self::$pipes as $pipe) {
            fclose($pipe);
        }

        proc_close(self::$server);
    }

    public function getClients()
    {
        $parser = array(
            new FXMLRPC\Parser\NativeParser(),
            new FXMLRPC\Parser\XMLReaderParser(),
        );
        $serializer = array(
            new FXMLRPC\Serializer\NativeSerializer(),
            new FXMLRPC\Serializer\XMLWriterSerializer(),
        );


        $browserSocket = new \Buzz\Browser();
        $browserSocket->setClient(new \Buzz\Client\FileGetContents());

        $zf1HttpClientSocket = new \Zend_Http_Client();
        $zf1HttpClientSocket->setAdapter(new \Zend_Http_Client_Adapter_Socket());

        $zf2HttpClientSocket = new \Zend\Http\Client();
        $zf2HttpClientSocket->setAdapter(new \Zend\Http\Client\Adapter\Socket());

        $transports = array(
            new FXMLRPC\Transport\StreamSocketTransport(),
            new FXMLRPC\Transport\BuzzBrowserBridge($browserSocket),
            new FXMLRPC\Transport\ZF1HttpClientBridge($zf1HttpClientSocket),
            new FXMLRPC\Transport\ZF2HttpClientBridge($zf2HttpClientSocket),
        );

        if (extension_loaded('curl')) {
            $browserCurl = new \Buzz\Browser();
            $browserCurl->setClient(new \Buzz\Client\Curl());
            $transports[] = new FXMLRPC\Transport\BuzzBrowserBridge($browserCurl);

            $zf1HttpClientCurl = new \Zend_Http_Client();
            $zf1HttpClientCurl->setAdapter(new \Zend_Http_Client_Adapter_Curl());
            $transports[] = new FXMLRPC\Transport\ZF1HttpClientBridge($zf1HttpClientCurl);

            $zf2HttpClientCurl = new \Zend\Http\Client();
            $zf2HttpClientCurl->setAdapter(new \Zend\Http\Client\Adapter\Curl());
            $transports[] = new FXMLRPC\Transport\ZF2HttpClientBridge($zf2HttpClientCurl);

            $guzzle = new \Guzzle\Http\Client();
            $transports[] = new FXMLRPC\Transport\GuzzleBridge($guzzle);
        }

        if (extension_loaded('http')) {
            $transports[] = new FXMLRPC\Transport\PeclHttpBridge(new \HttpRequest());
        }

        $this->generateAllPossibleCombinations(array($transports, $parser, $serializer));

        return $this->clients;
    }

    private function generateAllPossibleCombinations($combinations)
    {
        if ($combinations) {
            for ($i = 0; $i < count($combinations[0]); ++$i) {
                $temp = $combinations;
                $this->dependencies[$this->pos] = $combinations[0][$i];
                array_shift($temp);
                $this->pos++;
                $this->generateAllPossibleCombinations($temp);
            }
        } else {
            $this->clients[] = array(
                new FXMLRPC\Client(
                    'http://localhost:9090/',
                    $this->dependencies[0],
                    $this->dependencies[1],
                    $this->dependencies[2]
                ),
                $this->dependencies[0],
                $this->dependencies[1],
                $this->dependencies[2]
            );
        }
        $this->pos--;
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
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testString($client)
    {
        $result = 'HELLO WORLD <> &';
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testBase64($client)
    {
        $expected = new FXMLRPC\Value\Base64('HELLO WORLD');
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
                new \DateTime('2012-02-03 20:11:15', new \DateTimeZone('UTC')),
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
        } catch (FXMLRPC\Exception\ResponseException $e) {
            $this->assertSame('ERROR', $e->getMessage());
            $this->assertSame('ERROR', $e->getFaultString());
            $this->assertSame(0, $e->getCode());
            $this->assertSame(123, $e->getFaultCode());
        }
    }

    /**
     * @dataProvider getClients
     */
    public function testServerReturnsInvalidResult($client)
    {
        $client->setUri('http://localhost:9091/');

        try {
            $client->call('system.failure');
            $this->fail('Exception expected');
        } catch (\FXMLRPC\Exception\HttpException $e) {
            $this->assertInstanceOf('FXMLRPC\Exception\TransportException', $e);
            $this->assertInstanceOf('FXMLRPC\Exception\ExceptionInterface', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('An HTTP error occured', $e->getMessage());
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
        } catch (\FXMLRPC\Exception\TcpException $e) {
            $this->assertInstanceOf('FXMLRPC\Exception\TransportException', $e);
            $this->assertInstanceOf('FXMLRPC\Exception\ExceptionInterface', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('A transport error occured', $e->getMessage());
            $this->assertSame(0, $e->getCode());
        }
    }
}
