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

class NullDecorator extends AbstractDecorator
{
}

class AbstractDecoratorTest extends \PHPUnit_Framework_TestCase
{
    /** @var ClientInterface|MockObject */
    private $wrapped;

    /** @var AbstractDecorator */
    private $decorator;

    public function setUp()
    {
        $this->wrapped = $this
            ->getMockBuilder('fXmlRpc\ClientInterface')
            ->getMock();
        $this->decorator = new NullDecorator($this->wrapped);
    }

    public function testCallInvokesWrappedInstance()
    {
        $this->wrapped
            ->expects($this->once())
            ->method('call')
            ->with('method', array('arg1', 'arg2'))
            ->will($this->returnValue('response'));
        $this->assertSame('response', $this->decorator->call('method', array('arg1', 'arg2')));
    }
    public function testMulticallMethodWrapped()
    {
        $this->wrapped
            ->expects($this->once())
            ->method('multicall')
            ->will($this->returnValue('m'));

        $this->assertSame('m', $this->decorator->multicall());
    }
}
