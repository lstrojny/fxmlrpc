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

abstract class AbstractCombinatoricsClientTest extends \PHPUnit_Framework_TestCase
{
    protected $disabledExtensions = array();

    private $pos = 0;

    private $clients = array();

    private $clientDependencies = array();

    protected $endpoint;

    protected $clientsLimit = 0;

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

        if ($this->clientsLimit !== 0) {
            shuffle($this->clients);
            $this->clients = array_slice($this->clients, 0, $this->clientsLimit);
        }

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
        $serializer = array();

        $nativeSerializer = new fXmlRpc\Serializer\NativeSerializer();
        $serializer[] = $nativeSerializer;

        if ($this->extensionEnabled('nil')) {
            $xmlWriterSerializer = new fXmlRpc\Serializer\XmlWriterSerializer();
            $xmlWriterSerializer->enableExtension('nil');
            $serializer[] = $xmlWriterSerializer;
        }

        $xmlWriterNilExtensionDisabled = new fXmlRpc\Serializer\XmlWriterSerializer();
        $xmlWriterNilExtensionDisabled->disableExtension('nil');
        $serializer[] = $xmlWriterNilExtensionDisabled;

        return $serializer;
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
                $this->endpoint,
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

    protected function extensionEnabled($extension)
    {
        return !in_array($extension, $this->disabledExtensions, true);
    }
}
