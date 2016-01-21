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

use fXmlRpc\MulticallClientInterface;

/**
 * @large
 * @group integration
 * @group python
 */
class MulticallBuilderIntegrationBasedIntegrationTest extends AbstractIntegrationTest
{
    protected static $enabled = false;

    protected static $endpoint = 'http://127.0.0.1:28000';

    protected static $errorEndpoint = 'http://127.0.0.1:28001';

    protected static $command = 'exec python server.py';

    /** @var mixed */
    private $expected;

    /** @var integer */
    private $handlerInvoked = 0;

    public function setUp()
    {
        $this->markTestSkipped('Multicall integration tests need to be ported to node.js');
    }

    /** @dataProvider getClients */
    public function testMulticallWithError(MulticallClientInterface $client)
    {
        $this->handlerInvoked = 0;
        $this->expected = array(
            array(
                'faultCode'   => 1,
                'faultString' => '<type \'exceptions.Exception\'>:method "invalidMethod" is not supported'
            )
        );

        $result = $client->multicall()
            ->addCall('invalidMethod')
            ->onError(array($this, 'handler'))
            ->execute();

        $this->assertSame(1, $this->handlerInvoked);
        $this->assertSame($this->expected, $result);
    }

    /** @dataProvider getClients */
    public function testSimpleMulticall(MulticallClientInterface $client)
    {
        $this->handlerInvoked = 0;
        $this->expected = array(
            array(0),
            array(1),
            array(2),
            array(3),
            array(4),
        );

        $result = $client->multicall()
            ->addCall('system.echo', array(0))
            ->addCall('system.echo', array(1))
            ->addCall('system.echo', array(2))
            ->addCall('system.echo', array(3))
            ->addCall('system.echo', array(4))
            ->onSuccess(array($this, 'handler'))
            ->execute();

        $this->assertSame($this->expected, $result);
        $this->assertSame(5, $this->handlerInvoked);
    }

    public function handler($result)
    {
        $this->handlerInvoked++;
        $this->assertSame(current($this->expected), $result);
        next($this->expected);
    }
}
