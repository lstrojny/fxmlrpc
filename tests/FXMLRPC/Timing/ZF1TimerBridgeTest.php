<?php
/**
 * Copyright (C) 2012
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

namespace FXMLRPC\Timing;

use FXMLRPC\Timing\ZF1TimerBridge;
use Zend_Log;

class ZF1TimerBridgeTest extends \PHPUnit_Framework_TestCase
{
    private $log;

    public function setUp()
    {
        $this->log = $this->getMockBuilder('Zend_Log')
            ->disableOriginalClone()
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDefaultLogging()
    {
        $bridge = new ZF1TimerBridge($this->log);
        $this->log
            ->expects($this->once())
            ->method('log')
            ->with('FXMLRPC call took 0.1000000000s', Zend_Log::DEBUG, array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1')));


        $bridge->recordTiming(0.1, 'method', array('arg1'));
    }

    public function testWithCustomLogLevel()
    {
        $bridge = new ZF1TimerBridge($this->log, Zend_Log::ALERT);
        $this->log
            ->expects($this->once())
            ->method('log')
            ->with('FXMLRPC call took 0.1000000000s', Zend_Log::ALERT, array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1')));

        $bridge->recordTiming(0.1, 'method', array('arg1'));
    }

    public function testWithCustomMessageTemplate()
    {
        $bridge = new ZF1TimerBridge($this->log, null, 'Custom template %2.1Fs');
        $this->log
            ->expects($this->once())
            ->method('log')
            ->with('Custom template 0.1s', Zend_Log::DEBUG, array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1')));

        $bridge->recordTiming(0.1, 'method', array('arg1'));
    }
}