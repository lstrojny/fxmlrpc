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
namespace fXmlRpc\Transport;

use Exception;
use fXmlRpc\Client;
use PHPUnit_Framework_MockObject_MockObject as MockObject;
use PHPUnit_Framework_MockObject_Matcher_Invocation as InvocationMatcher;

/**
 * Class RecorderTest
 *
 * @author Piotr Olaszewski <piotroo89 [%] gmail dot com>
 */
class RecorderTest extends \PHPUnit_Framework_TestCase
{
    /** @var TransportInterface|MockObject */
    private $transport;

    /** @var Recorder */
    private $recorder;

    /** @var Client */
    private $client;

    /** @var Exception */
    private $exception;

    private $expectedRequest = '<?xml version="1.0" encoding="UTF-8"?>
<methodCall>
    <methodName>TestMethod</methodName>
    <params>
        <param><value><string>param1</string></value></param>
        <param><value><int>2</int></value></param>
        <param><value><struct><member><name>param3</name><value><boolean>1</boolean></value></member></struct></value></param>
    </params>
</methodCall>';

    private $expectedResponse = '<?xml version="1.0"?>
<methodResponse>
    <params>
        <param><value><string>Returned string</string></value></param>
    </params>
</methodResponse>';

    public function setUp()
    {
        parent::setUp();
        $this->transport = $this->getMock('fXmlRpc\Transport\TransportInterface');
        $this->recorder = new Recorder($this->transport);
        $this->client = new Client('http://foo.com', $this->recorder);
        $this->exception = new Exception();
    }

    public function testReturnLastRequest()
    {
        $this->transportOk();
        $this->client->call('TestMethod', ['param1', 2, ['param3' => true]]);

        $lastRequest = $this->recorder->getLastRequest();

        $this->assertXmlStringEqualsXmlString($this->expectedRequest, $lastRequest);
    }

    public function testReturnLastResponse()
    {
        $this->transportOk();
        $this->client->call('TestMethod', ['param1', 2, ['param3' => true]]);

        $lastResponse = $this->recorder->getLastResponse();

        $this->assertXmlStringEqualsXmlString($this->expectedResponse, $lastResponse);
    }

    public function testReturnXmlForRequestAndNullForResponseWhenTransportThrowsException()
    {
        try {
            $this->transportFail();
            $this->client->call('TestMethod', ['param1', 2, ['param3' => true]]);
        } catch (Exception $e) {
            $this->assertSame($this->exception, $e);
        }

        $lastRequest = $this->recorder->getLastRequest();
        $lastResponse = $this->recorder->getLastResponse();

        $this->assertXmlStringEqualsXmlString($this->expectedRequest, $lastRequest);
        $this->assertNull($lastResponse);
    }

    public function testReturnLastException()
    {
        try {
            $this->transportFail();
            $this->client->call('TestMethod', ['param1', 2, ['param3' => true]]);
        } catch (Exception $e) {
            $this->assertSame($this->exception, $e);
        }

        $this->assertSame($this->exception, $this->recorder->getLastException());
    }

    public function testIsLastResponseNotContainXmlFromPreviousRequest()
    {
        $this->transportOk($this->at(0));
        $this->transportFail($this->at(1));
        $this->client->call('TestMethod', ['param1', 2, ['param3' => true]]);

        $this->assertXmlStringEqualsXmlString($this->expectedRequest, $this->recorder->getLastRequest());
        $this->assertXmlStringEqualsXmlString($this->expectedResponse, $this->recorder->getLastResponse());

        try {
            $this->client->call('TestMethod', ['param1', 2, ['param3' => true]]);
        } catch (Exception $e) {
            $this->assertSame($this->exception, $e);
        }

        $lastRequest = $this->recorder->getLastRequest();
        $lastResponse = $this->recorder->getLastResponse();
        $lastException = $this->recorder->getLastException();

        $this->assertXmlStringEqualsXmlString($this->expectedRequest, $lastRequest);
        $this->assertNull($lastResponse);
        $this->assertSame($this->exception, $lastException);
    }

    private function transportOk(InvocationMatcher $matcher = null)
    {
        $matcher = $matcher ?: $this->once();
        $this->transport
            ->expects($matcher)
            ->method('send')
            ->willReturn($this->expectedResponse);
    }

    private function transportFail(InvocationMatcher $matcher = null)
    {
        $matcher = $matcher ?: $this->once();
        $this->transport
            ->expects($matcher)
            ->method('send')
            ->willThrowException($this->exception);
    }
}
