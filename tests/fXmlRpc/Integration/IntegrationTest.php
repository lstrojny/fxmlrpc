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

namespace fXmlPRC\Integration;

use fXmlRpc;
use Exception;

/**
 * @large
 */
class IntegrationTest extends \PHPUnit_Framework_TestCase
{
    private $clients = array();

    private $clientDependencies = array();

    private $pos = 0;

    private static $server;

    private static $pipes;

    public static function setUpBeforeClass()
    {
        self::$server = proc_open(
            'node server.js',
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
        $this->generateAllPossibleCombinations(
            array(
                $this->getTransports(),
                $this->getParsers(),
                $this->getSerializers(),
                $this->getTimerBridges()
            )
        );

        return $this->clients;
    }

    private function getParsers()
    {
        return array(
            new fXmlRpc\Parser\NativeParser(),
            new fXmlRpc\Parser\XmlReaderParser(),
        );
    }

    private function getSerializers()
    {
        $nativeSerializer = new fXmlRpc\Serializer\NativeSerializer();

        $xmlWriterSerializer = new fXmlRpc\Serializer\XmlWriterSerializer();

        $xmlWriterNilExtensionDisabled = new fXmlRpc\Serializer\XmlWriterSerializer();
        $xmlWriterNilExtensionDisabled->disableExtension('nil');

        return array(
            $nativeSerializer,
            $xmlWriterSerializer,
            $xmlWriterNilExtensionDisabled,
        );
    }

    private function getTransports()
    {
        $browserSocket = new \Buzz\Browser();
        $browserSocket->setClient(new \Buzz\Client\FileGetContents());

        $zendFrameworkOneHttpClientSocket = new \Zend_Http_Client();
        $zendFrameworkOneHttpClientSocket->setAdapter(new \Zend_Http_Client_Adapter_Socket());

        $zendFrameworkOneHttpClientProxy = new \Zend_Http_Client();
        $zendFrameworkOneHttpClientProxy->setAdapter(new \Zend_Http_Client_Adapter_Proxy());

        $zendFrameworkTwoHttpClientSocket = new \Zend\Http\Client();
        $zendFrameworkTwoHttpClientSocket->setAdapter(new \Zend\Http\Client\Adapter\Socket());

        $zendFrameworkTwoHttpClientProxy = new \Zend\Http\Client();
        $zendFrameworkTwoHttpClientProxy->setAdapter(new \Zend\Http\Client\Adapter\Proxy());

        $transports = array(
            new fXmlRpc\Transport\StreamSocketTransport(),
            new fXmlRpc\Transport\BuzzBrowserBridge($browserSocket),
            new fXmlRpc\Transport\ZendFrameworkOneHttpClientBridge($zendFrameworkOneHttpClientSocket),
            new fXmlRpc\Transport\ZendFrameworkOneHttpClientBridge($zendFrameworkOneHttpClientProxy),
            new fXmlRpc\Transport\ZendFrameworkTwoHttpClientBridge($zendFrameworkTwoHttpClientSocket),
            new fXmlRpc\Transport\ZendFrameworkTwoHttpClientBridge($zendFrameworkTwoHttpClientProxy),
        );

        if (extension_loaded('curl')) {
            $browserCurl = new \Buzz\Browser();
            $browserCurl->setClient(new \Buzz\Client\Curl());
            $transports[] = new fXmlRpc\Transport\BuzzBrowserBridge($browserCurl);

            $zendFrameworkOneHttpClientCurl = new \Zend_Http_Client();
            $zendFrameworkOneHttpClientCurl->setAdapter(new \Zend_Http_Client_Adapter_Curl());
            $transports[] = new fXmlRpc\Transport\ZendFrameworkOneHttpClientBridge($zendFrameworkOneHttpClientCurl);

            $zendFrameworkTwoHttpClientCurl = new \Zend\Http\Client();
            $zendFrameworkTwoHttpClientCurl->setAdapter(new \Zend\Http\Client\Adapter\Curl());
            $transports[] = new fXmlRpc\Transport\ZendFrameworkTwoHttpClientBridge($zendFrameworkTwoHttpClientCurl);

            $guzzle = new \Guzzle\Http\Client();
            $transports[] = new fXmlRpc\Transport\GuzzleBridge($guzzle);

            $transports[] = new fXmlRpc\Transport\CurlTransport();
        }

        if (extension_loaded('http')) {
            $transports[] = new fXmlRpc\Transport\PeclHttpBridge(new \HttpRequest());
        }

        return $transports;
    }

    private function getTimerBridges()
    {
        $zendFrameworkOneLogger = new \Zend_Log(new \Zend_Log_Writer_Null());

        $zendFrameworkTwoLogger = new \Zend\Log\Logger();
        $zendFrameworkTwoLogger->addWriter(new \Zend\Log\Writer\Null());

        $monolog = new \Monolog\Logger('test');
        $monolog->pushHandler(new \Monolog\Handler\NullHandler());

        return array(
            new \fXmlRpc\Timing\ZendFrameworkOneTimerBridge($zendFrameworkOneLogger),
            new \fXmlRpc\Timing\ZendFrameworkTwoTimerBridge($zendFrameworkTwoLogger),
            new \fXmlRpc\Timing\MonologTimerBridge($monolog),
            null
        );
    }

    private function generateAllPossibleCombinations($combinations)
    {
        if ($combinations) {
            for ($i = 0; $i < count($combinations[0]); ++$i) {
                $temp = $combinations;
                $this->clientDependencies[$this->pos] = $combinations[0][$i];
                array_shift($temp);
                $this->pos++;
                $this->generateAllPossibleCombinations($temp);
            }
        } else {
            $client = new fXmlRpc\Client(
                'http://localhost:9090/',
                $this->clientDependencies[0],
                $this->clientDependencies[1],
                $this->clientDependencies[2]
            );
            if ($this->clientDependencies[3]) {
                $client = new \fXmlRpc\Timing\TimingDecorator($client, $this->clientDependencies[3]);
            }
            $this->clients[] = array($client, $this->clientDependencies[0], $this->clientDependencies[1], $this->clientDependencies[2]);
        }
        $this->pos--;
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
        $this->assertSame($result, $client->call('system.echo', array($result)));
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
        $expected = fXmlRpc\Value\Base64::deserialize('HELLO WORLD');
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
        } catch (fXmlRpc\Exception\ResponseException $e) {
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
