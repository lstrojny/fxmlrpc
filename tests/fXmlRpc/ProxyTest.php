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

namespace fXmlRpc;

use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ProxyTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClientInterface|MockObject */
    private $client;

    /** @var Proxy */
    private $proxy;

    public function setUp()
    {
        $this->client = $this->getMockBuilder('fXmlRpc\ClientInterface')
            ->getMock();
        $this->proxy = new Proxy($this->client);
    }

    public function testCallingMethod()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with('method', array('arg1', 'arg2'))
            ->will($this->returnValue('VALUE'));

        $this->assertSame('VALUE', $this->proxy->method('arg1', 'arg2'));
    }

    public function testCallingNamespaceMethod()
    {
        $this->client
            ->expects($this->at(0))
            ->method('call')
            ->with('namespace.method', array('arg1', 'arg2'))
            ->will($this->returnValue('namespace method return'));

        $this->client
            ->expects($this->at(1))
            ->method('call')
            ->with('namespace.another_namespace.method', array('arg1', 'arg2'))
            ->will($this->returnValue('another namespace method return first'));

        $this->client
            ->expects($this->at(2))
            ->method('call')
            ->with('namespace.another_namespace.method', array('arg1', 'arg2'))
            ->will($this->returnValue('another namespace method return second'));

        $this->assertSame('namespace method return', $this->proxy->namespace->method('arg1', 'arg2'));
        $this->assertSame('another namespace method return first', $this->proxy->namespace->another_namespace->method('arg1', 'arg2'));
        $this->assertSame('another namespace method return second', $this->proxy->{"namespace.another_namespace.method"}('arg1', 'arg2'));
    }

    public function testCallingNamespaceMethodWithCustomSeparator()
    {
        $proxy = new Proxy($this->client, '_');
        $this->client
            ->expects($this->at(0))
            ->method('call')
            ->with('namespace_method', array(1, 2))
            ->will($this->returnValue('namespace method return'));
        $this->client
            ->expects($this->at(1))
            ->method('call')
            ->with('namespace_another_namespace_method', array(1, 2))
            ->will($this->returnValue('another namespace method return'));

        $this->assertSame('namespace method return', $proxy->namespace->method(1, 2));
        $this->assertSame('another namespace method return', $proxy->namespace->another_namespace->method(1, 2));
    }

    public function testLazyLoading()
    {
        $this->assertSame($this->proxy->foo, $this->proxy->foo);
    }
}
