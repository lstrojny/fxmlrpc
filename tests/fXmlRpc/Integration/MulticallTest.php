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

use fXmlRpc\ClientInterface;

/**
 * @large
 * @group integration
 * @group external
 */
class MulticallTest extends AbstractCombinatoricsClientTest
{
    /**
     * @var string
     */
    protected $endpoint = 'http://betty.userland.com/RPC2';

    /**
     * @var mixed
     */
    private $expected;

    /**
     * @var int
     */
    private $handlerInvoked = 0;

    /**
     * @var int
     */
    protected $clientsLimit = 5;

    /**
     * @dataProvider getClients
     */
    public function testMulticallWithError(ClientInterface $client)
    {
        $this->expected = $expected = array(
            array(
                'faultCode'   => 7,
                'faultString' => 'Can\'t evaluate the expression because the name "invalidMethod" hasn\'t been defined.'
            )
        );

        $result = $client->multicall()
            ->addCall('examples.invalidMethod')
            ->onError(array($this, 'handler'))
            ->execute();

        $this->assertSame(1, $this->handlerInvoked);
        $this->assertSame($expected, $result);
    }

    /**
     * @dataProvider getClients
     */
    public function testSimpleMulticall(ClientInterface $client)
    {
        $this->expected = $expected = array(
            array('Idaho'),
            array('Michigan'),
            array('New York'),
            array('Tennessee')
        );

        $result = $client->multicall()
            ->addCall('examples.getStateName', array(12))
            ->addCall('examples.getStateName', array(22))
            ->addCall('examples.getStateName', array(32))
            ->addCall('examples.getStateName', array(42))
            ->onSuccess(array($this, 'handler'))
            ->execute();

        $this->assertSame($expected, $result);
        $this->assertSame(4, $this->handlerInvoked);
    }

    public function handler($result)
    {
        $this->handlerInvoked++;
        $this->assertSame(array_shift($this->expected), $result);
    }
}
