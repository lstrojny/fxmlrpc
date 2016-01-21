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

use fXmlRpc\Client;

/**
 * @large
 * @group integration
 * @group node
 */
class NodeCallClientIntegrationTest extends AbstractCallClientIntegrationTest
{
    protected static $endpoint = 'http://127.0.0.1:9090/';

    protected static $errorEndpoint = 'http://127.0.0.1:9091/';

    protected static $command = 'exec node server.js';

    /** @dataProvider getClientsOnly */
    public function testServerNotReachableViaTcpIp(Client $client)
    {
        $client->setUri('http://127.0.0.1:12345/');

        try {
            $client->call('system.failure');
            $this->fail('Exception expected');
        } catch (\fXmlRpc\Exception\TransportException $e) {
            $this->assertInstanceOf('fXmlRpc\Exception\TransportException', $e);
            $this->assertInstanceOf('fXmlRpc\Exception\ExceptionInterface', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('Transport error occurred:', $e->getMessage());
            $this->assertSame(0, $e->getCode());
        }
    }

    /** @dataProvider getClientsOnly */
    public function testServerReturnsInvalidResult(Client $client)
    {
        $this->executeSystemFailureTest($client);
    }
}
