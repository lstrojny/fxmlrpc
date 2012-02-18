<?php
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
        sleep(1);
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

        $browserCurl = new \Buzz\Browser();
        $browserCurl->setClient(new \Buzz\Client\Curl());

        $zf1HttpClientSocket = new \Zend_Http_Client();
        $zf1HttpClientSocket->setAdapter(new \Zend_Http_Client_Adapter_Socket());

        $zf1HttpClientCurl = new \Zend_Http_Client();
        $zf1HttpClientCurl->setAdapter(new \Zend_Http_Client_Adapter_Curl());

        $zf2HttpClientSocket = new \Zend\Http\Client();
        $zf2HttpClientSocket->setAdapter(new \Zend\Http\Client\Adapter\Socket());

        $zf2HttpClientCurl = new \Zend\Http\Client();
        $zf2HttpClientCurl->setAdapter(new \Zend\Http\Client\Adapter\Curl());

        $transports = array(
            new FXMLRPC\Transport\StreamSocketTransport(),
            new FXMLRPC\Transport\BuzzBrowserBridge($browserSocket),
            new FXMLRPC\Transport\BuzzBrowserBridge($browserCurl),
            new FXMLRPC\Transport\ZF1HttpClientBridge($zf1HttpClientSocket),
            new FXMLRPC\Transport\ZF1HttpClientBridge($zf1HttpClientCurl),
            new FXMLRPC\Transport\ZF2HttpClientBridge($zf2HttpClientSocket),
            new FXMLRPC\Transport\ZF2HttpClientBridge($zf2HttpClientCurl),
        );

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
        $result = 'HELLO WORLD';
        $this->assertSame($result, $client->call('system.echo', array($result)));
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
        $result = new \DateTime('2011-01-12 23:12:10');
        $this->assertSame(
            $result->format('Y-m-d H:i:s'),
            $client->call('system.echo', array($result))->format('Y-m-d H:i:s')
        );
    }

    /**
     * @dataProvider getClients
     */
    public function testComplexStruct($client)
    {
        $result = array(
            'el1' => array(
                'one', 'two', 'three'
            ),
            'el2' => array('first' => 'one', 'second' => 'two', 'third' => 'three'),
            'el3' => range(1, 100)
        );
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /**
     * @dataProvider getClients
     */
    public function testServerReturnsInvalidResult($client)
    {
        $client->setUri('http://localhost:9091/');

        try {
            $client->call('system.failure');
            $this->fail('Expected exception');
        } catch (Exception $e) {
            $this->assertStringStartsWith('HTTP error: ', $e->getMessage());
        }
    }
}
