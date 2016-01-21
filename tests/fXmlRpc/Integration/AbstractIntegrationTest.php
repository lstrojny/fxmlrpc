<?php
/**
 * Copyright (C) 2012-2016
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

namespace fXmlRpc\Integration;

use fXmlRpc;
use hmmmath\Fibonacci\FibonacciFactory;
use Symfony\Component\Process\Process;

abstract class AbstractIntegrationTest extends AbstractClientBasedIntegrationTest
{
    /** @var boolean */
    protected static $enabled = true;

    /** @var string */
    protected static $command;

    /** @var Process */
    protected static $server;

    /** @var string */
    protected static $errorEndpoint;

    /** @var integer */
    protected static $restartThreshold = 100;

    private static $runCount = 0;

    protected static function startServer()
    {
        if (!static::$enabled) {
            return;
        }
        self::$server = new Process(static::$command . ' &>/dev/null', __DIR__ . '/Fixtures');
        self::$server->start();
        static::pollWait();
    }

    protected static function stopServer()
    {
        if (!static::$enabled) {
            return;
        }
        self::$server->stop();
    }

    public static function setUpBeforeClass()
    {
        static::startServer();
    }

    private static function pollWait()
    {
        $parts = parse_url(static::$endpoint);
        foreach (FibonacciFactory::sequence(50000, 10000, 10) as $offset => $sleepTime) {
            usleep($sleepTime);

            $socket = @fsockopen($parts['host'], $parts['port'], $errorNumber, $errorString, 1);
            if ($socket !== false) {
                fclose($socket);
                return;
            }
        }
    }

    public static function tearDownAfterClass()
    {
        static::stopServer();
    }

    public function setUp()
    {
        if (static::$restartThreshold > 0 && ++self::$runCount !== static::$restartThreshold) {
            return;
        }

        self::$runCount = 0;
        static::stopServer();
        static::startServer();
        static::pollWait();
    }

    protected $disabledExtensions = array();

    private $pos = 0;

    private $dependencyGraph = array();

    protected static $endpoint;

    public function getClients()
    {
        $clients = [];
        $this->generateAllPossibleCombinations(
            array(
                $this->getTransport(),
                $this->getParsers(),
                $this->getSerializers(),
                $this->getTimerBridges(),
            ),
            $clients
        );

        return $clients;
    }

    public function getClientsOnly()
    {
        $clients = [];
        $this->generateAllPossibleCombinations(
            array(
                $this->getTransport(),
                $this->getParsers(),
                $this->getSerializers(),
            ),
            $clients
        );

        return $clients;
    }

    private function getParsers()
    {
        $parser = array();

        if (extension_loaded('xmlrpc')) {
            $parser[] = new fXmlRpc\Parser\NativeParser();
        }

        $parser[] = new fXmlRpc\Parser\XmlReaderParser();

        return $parser;
    }

    private function getSerializers()
    {
        $serializer = array();

        if (extension_loaded('xmlrpc')) {
            $serializer[] = new fXmlRpc\Serializer\NativeSerializer();
        }


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

    private function getTransport()
    {
        return [new \fXmlRpc\Transport\HttpAdapterTransport(\Ivory\HttpAdapter\HttpAdapterFactory::guess())];
    }

    private function generateAllPossibleCombinations(array $combinations, array &$clients)
    {
        if ($combinations) {
            for ($i = 0; $i < count($combinations[0]); ++$i) {
                $temp = $combinations;
                $this->dependencyGraph[$this->pos] = $combinations[0][$i];
                array_shift($temp);
                $this->pos++;
                $this->generateAllPossibleCombinations($temp, $clients);
            }
        } else {
            $client = new fXmlRpc\Client(
                static::$endpoint,
                $this->dependencyGraph[0],
                $this->dependencyGraph[1],
                $this->dependencyGraph[2]
            );
            if (isset($this->dependencyGraph[3])) {
                $client = new \fXmlRpc\Timing\TimingDecorator($client, $this->dependencyGraph[3]);
            }
            $clients[] = array($client, $this->dependencyGraph[0], $this->dependencyGraph[1], $this->dependencyGraph[2]);
        }
        $this->pos--;
    }

    protected function extensionEnabled($extension)
    {
        return !in_array($extension, $this->disabledExtensions, true);
    }
}
