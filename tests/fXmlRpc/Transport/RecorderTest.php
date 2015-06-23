<?php
/**
 * Copyright (C) 2012-2015
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

use fXmlRpc\Client;

/**
 * Class RecorderTest
 * @author Piotr Olaszewski <piotroo89 [%] gmail dot com>
 */
class RecorderTest extends \PHPUnit_Framework_TestCase
{
    /** @var Recorder */
    private $recorder;
    private $expectedResponse = '<?xml version="1.0"?><methodResponse><params><param><value><string>Returned string</string></value></param></params></methodResponse>';

    public function setUp()
    {
        parent::setUp();
        $transport = $this->getMock('fXmlRpc\Transport\TransportInterface');
        $transport
            ->expects($this->once())
            ->method('send')
            ->willReturn($this->expectedResponse);
        $this->recorder = new Recorder($transport);
    }

    public function testReturnLastRequest()
    {
        $client = new Client('http://foo.com', $this->recorder);
        $client->call('TestMethod', ['param1', 2, ['param3' => true]]);

        $lastRequest = $this->recorder->getLastRequest();

        $expected = '<?xml version="1.0" encoding="UTF-8"?><methodCall><methodName>TestMethod</methodName><params><param><value><string>param1</string></value></param><param><value><int>2</int></value></param><param><value><struct><member><name>param3</name><value><boolean>1</boolean></value></member></struct></value></param></params></methodCall>';
        $this->assertEquals($expected, $lastRequest);
    }

    public function testReturnLastResponse()
    {
        $client = new Client('http://foo.com', $this->recorder);
        $client->call('TestMethod', ['param1', 2, ['param3' => true]]);

        $lastResponse = $this->recorder->getLastResponse();

        $this->assertEquals($this->expectedResponse, $lastResponse);
    }
}
