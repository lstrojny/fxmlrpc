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

use Monolog\Logger;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class MonologTimerBridgeTest extends \PHPUnit_Framework_TestCase
{
    /** @var Logger|MockObject */
    private $monolog;

    public function setUp()
    {
        $this->monolog = $this
            ->getMockBuilder('Monolog\Logger')
            ->disableOriginalConstructor()
            ->getMock();
    }

    public function testDelegatesLoggingToMonolog()
    {
        $bridge = new MonologTimerBridge($this->monolog);
        $this->monolog
            ->expects($this->once())
            ->method('addRecord')
            ->with(
                Logger::DEBUG,
                'fXmlRpc call took 1.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSettingCustomLogLevel()
    {
        $bridge = new MonologTimerBridge($this->monolog, Logger::ALERT);
        $this->monolog
            ->expects($this->once())
            ->method('addRecord')
            ->with(
                Logger::ALERT,
                'fXmlRpc call took 1.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSetEmptyLogLevels()
    {
        $bridge = new MonologTimerBridge($this->monolog, []);
        $this->monolog
            ->expects($this->once())
            ->method('addRecord')
            ->with(Logger::DEBUG);

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSettingCustomMessageTemplate()
    {
        $bridge = new MonologTimerBridge($this->monolog, null, 'Custom template %2.1Fs');
        $this->monolog
            ->expects($this->once())
            ->method('addRecord')
            ->with(
                Logger::DEBUG,
                'Custom template 1.1s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSpecifyingLoggingThresholds()
    {
        $bridge = new MonologTimerBridge($this->monolog, array(1 => Logger::DEBUG, 2 => Logger::WARNING, '3.5' => Logger::ALERT));
        $this->monolog
            ->expects($this->at(0))
            ->method('addRecord')
            ->with(
                Logger::DEBUG,
                'fXmlRpc call took 0.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->monolog
            ->expects($this->at(1))
            ->method('addRecord')
            ->with(
                Logger::DEBUG,
                'fXmlRpc call took 1.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->monolog
            ->expects($this->at(2))
            ->method('addRecord')
            ->with(
                Logger::WARNING,
                'fXmlRpc call took 2.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->monolog
            ->expects($this->at(3))
            ->method('addRecord')
            ->with(
                Logger::ALERT,
                'fXmlRpc call took 3.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->monolog
            ->expects($this->at(4))
            ->method('addRecord')
            ->with(
                Logger::ALERT,
                'fXmlRpc call took 5.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );

        $bridge->recordTiming(0.1, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(2.5, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(3.5, 'method', array('arg1', 'arg2'));
        $bridge->recordTiming(5.5, 'method', array('arg1', 'arg2'));
    }
}
