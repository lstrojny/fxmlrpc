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

use Psr\Log\LoggerInterface;
use Psr\Log\LogLevel;
use PHPUnit_Framework_MockObject_MockObject as MockObject;

class Psr3TimerBridgeTest extends \PHPUnit_Framework_TestCase
{
    /** @var LoggerInterface|MockObject */
    private $logger;

    public function setUp()
    {
        $this->logger = $this->getMock('Psr\Log\LoggerInterface');
    }

    public function testDelegatesLogging()
    {
        $bridge = new Psr3TimerBridge($this->logger);
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::DEBUG,
                'fXmlRpc call took 1.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSettingCustomLogLevel()
    {
        $bridge = new Psr3TimerBridge($this->logger, LogLevel::ALERT);
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::ALERT,
                'fXmlRpc call took 1.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSettingEmptyLogLevel()
    {
        $bridge = new Psr3TimerBridge($this->logger, []);
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(LogLevel::DEBUG);

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSettingCustomMessageTemplate()
    {
        $bridge = new Psr3TimerBridge($this->logger, null, 'Custom template %2.1Fs');
        $this->logger
            ->expects($this->once())
            ->method('log')
            ->with(
                LogLevel::DEBUG,
                'Custom template 1.1s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );

        $bridge->recordTiming(1.1, 'method', array('arg1', 'arg2'));
    }

    public function testSpecifyingLoggingThresholds()
    {
        $bridge = new Psr3TimerBridge($this->logger, array(1 => LogLevel::DEBUG, 2 => LogLevel::WARNING, '3.5' => LogLevel::ALERT));
        $this->logger
            ->expects($this->at(0))
            ->method('log')
            ->with(
                LogLevel::DEBUG,
                'fXmlRpc call took 0.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->logger
            ->expects($this->at(1))
            ->method('log')
            ->with(
                LogLevel::DEBUG,
                'fXmlRpc call took 1.1000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->logger
            ->expects($this->at(2))
            ->method('log')
            ->with(
                LogLevel::WARNING,
                'fXmlRpc call took 2.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->logger
            ->expects($this->at(3))
            ->method('log')
            ->with(
                LogLevel::ALERT,
                'fXmlRpc call took 3.5000000000s',
                array('xmlrpcMethod' => 'method', 'xmlrpcArguments' => array('arg1', 'arg2'))
            );
        $this->logger
            ->expects($this->at(4))
            ->method('log')
            ->with(
                LogLevel::ALERT,
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
