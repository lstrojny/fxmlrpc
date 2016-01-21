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

namespace fXmlRpc\Timing;

use Zend\Log\LoggerInterface;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class ZendFrameworkTwoTimerBridgeTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoggerInterface|MockObject */
    private $log;

    public function setUp()
    {
        $this->log = $this->getMockBuilder('Zend\Log\LoggerInterface')
            ->disableOriginalClone()
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDefaultLogging()
    {
        $bridge = new ZendFrameworkTwoTimerBridge($this->log);
        $this->log
            ->expects($this->once())
            ->method('debug')
            ->with('fXmlRpc call took 0.1000000000s', array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1')));

        $bridge->recordTiming(0.1, 'method', array('arg1'));
    }

    public function testWithCustomLogLevel()
    {
        $bridge = new ZendFrameworkTwoTimerBridge($this->log, 'alert');
        $this->log
            ->expects($this->once())
            ->method('alert')
            ->with('fXmlRpc call took 0.1000000000s', array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1')));

        $bridge->recordTiming(0.1, 'method', array('arg1'));
    }

    public function testWithEmptyLogLevel()
    {
        $bridge = new ZendFrameworkTwoTimerBridge($this->log, []);
        $this->log
            ->expects($this->once())
            ->method('debug');

        $bridge->recordTiming(0.1, 'method', array('arg1'));
    }

    public function testWithCustomMessageTemplate()
    {
        $bridge = new ZendFrameworkTwoTimerBridge($this->log, null, 'Custom template %2.1Fs');
        $this->log
            ->expects($this->once())
            ->method('debug')
            ->with('Custom template 0.1s', array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1')));

        $bridge->recordTiming(0.1, 'method', array('arg1'));
    }

    public function testSpecifyingLoggingThresholds()
    {
        $bridge = new ZendFrameworkTwoTimerBridge($this->log, array(1 => 'debug', 2 => 'warn', '3.5' => 'alert'));
        $this->log
            ->expects($this->at(0))
            ->method('debug')
            ->with(
                'fXmlRpc call took 0.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2')
            )
        );
        $this->log
            ->expects($this->at(1))
            ->method('debug')
            ->with(
                'fXmlRpc call took 1.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2')
            )
        );
        $this->log
            ->expects($this->at(2))
            ->method('warn')
            ->with(
                'fXmlRpc call took 2.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2')
            )
        );
        $this->log
            ->expects($this->at(3))
            ->method('alert')
            ->with(
                'fXmlRpc call took 3.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2')
            )
        );
        $this->log
            ->expects($this->at(4))
            ->method('alert')
            ->with(
                'fXmlRpc call took 5.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2')
            )
        );

        $bridge->recordTiming(0.1, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(2.5, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(3.5, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(5.5, 'method', array('arg1', 'arg2'));
    }
}
