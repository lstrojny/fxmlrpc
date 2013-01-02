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

namespace fXmlRpc;

class MulticallTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var Multicall
     */
    private $multicall;

    public function setUp()
    {
        $this->client = $this->getMock('fXmlRpc\ClientInterface');
        $this->multicall = new Multicall($this->client);
    }

    public function testRetrievingMulticallResult()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                array(
                    array(
                        array('methodName' => 'method1', 'params' => array('arg11', 'arg12')),
                        array('methodName' => 'method2', 'params' => array('arg21', 'arg22')),
                    )
                )
            )
            ->will($this->returnValue(array('return1', 'return2')));

        $result = $this->multicall
            ->addCall('method1', array('arg11', 'arg12'))
            ->addCall('method2', array('arg21', 'arg22'))
            ->execute();

        $this->assertSame(array('return1', 'return2'), $result);
    }

    public function testIndividualSuccessHandlers()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                array(
                    array(
                        array('methodName' => 'method1', 'params' => array('arg11', 'arg12')),
                        array('methodName' => 'method2', 'params' => array('arg21', 'arg22')),
                        array('methodName' => 'method3', 'params' => array('arg31', 'arg32')),
                    )
                )
            )
            ->will($this->returnValue(array('return1', 'return2', array('faultCode' => 100))));

        $handlerResults = array();
        $handler = function ($result) use (&$handlerResults) {
            $handlerResults[] = $result;
        };
        $results = $this->multicall
            ->addCall('method1', array('arg11', 'arg12'))
            ->addCall('method2', array('arg21', 'arg22'), $handler)
            ->addCall('method3', array('arg31', 'arg32'), $handler)
            ->execute();

        $this->assertSame(array('return1', 'return2', array('faultCode' => 100)), $results);
        $this->assertSame(array('return2', array('faultCode' => 100)), $handlerResults);
    }

    public function testIndividualErrorHandler()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                array(
                    array(
                        array('methodName' => 'method1', 'params' => array('arg11', 'arg12')),
                        array('methodName' => 'method2', 'params' => array('arg21', 'arg22')),
                    )
                )
            )
            ->will($this->returnValue(array(array('faultCode' => 100), array('faultCode' => 200))));

        $handlerResults = array();
        $successHandler = function() {
            throw new \Exception('Should not be called');
        };
        $errorHandler = function ($result) use (&$handlerResults) {
            $handlerResults[] = $result;
        };
        $results = $this->multicall
            ->addCall('method1', array('arg11', 'arg12'), $successHandler, $errorHandler)
            ->addCall('method2', array('arg21', 'arg22'), null, $errorHandler)
            ->execute();

        $this->assertSame(array(array('faultCode' => 100), array('faultCode' => 200)), $results);
        $this->assertSame($results, $handlerResults);
    }

    public function testGlobalSuccessHandler()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                array(
                    array(
                        array('methodName' => 'method1', 'params' => array('arg11', 'arg12')),
                        array('methodName' => 'method2', 'params' => array('arg21', 'arg22')),
                    )
                )
            )
            ->will($this->returnValue(array('return1', array('faultCode' => 200))));

        $individualHandlerResults = array();
        $individualHandler = function ($result) use (&$individualHandlerResults) {
            $individualHandlerResults[] = $result;
        };
        $globalHandlerResults = array();
        $globalHandler = function ($result) use (&$globalHandlerResults) {
            $globalHandlerResults[] = $result;
        };
        $results = $this->multicall
            ->addCall('method1', array('arg11', 'arg12'), $individualHandler)
            ->addCall('method2', array('arg21', 'arg22'), $individualHandler)
            ->onSuccess($globalHandler)
            ->execute();

        $this->assertSame(array('return1', array('faultCode' => 200)), $results);
        $this->assertSame($results, $individualHandlerResults);
        $this->assertSame($results, $globalHandlerResults);
    }

    public function testGlobalErrorHandler()
    {
        $this->client
            ->expects($this->once())
            ->method('call')
            ->with(
                'system.multicall',
                array(
                    array(
                        array('methodName' => 'method1', 'params' => array('arg11', 'arg12')),
                        array('methodName' => 'method2', 'params' => array('arg21', 'arg22')),
                    )
                )
            )
            ->will($this->returnValue(array('return1', array('faultCode' => 200))));

        $individualSuccessHandlerResults = array();
        $individualSuccessHandler = function ($result) use (&$individualSuccessHandlerResults) {
            $individualSuccessHandlerResults[] = $result;
        };
        $globalSuccessHandlerResults = array();
        $globalSuccessHandler = function ($result) use (&$globalSuccessHandlerResults) {
            $globalSuccessHandlerResults[] = $result;
        };
        $globalErrorHandlerResults = array();
        $globalErrorHandler = function ($result) use (&$globalErrorHandlerResults) {
            $globalErrorHandlerResults[] = $result;
        };
        $results = $this->multicall
            ->addCall('method1', array('arg11', 'arg12'), $individualSuccessHandler)
            ->addCall('method2', array('arg21', 'arg22'), $individualSuccessHandler)
            ->onSuccess($globalSuccessHandler)
            ->onError($globalErrorHandler)
            ->execute();

        $this->assertSame(array('return1', array('faultCode' => 200)), $results);
        $this->assertSame($results, $individualSuccessHandlerResults);
        $this->assertSame(array('return1'), $globalSuccessHandlerResults);
        $this->assertSame(array(array('faultCode' => 200)), $globalErrorHandlerResults);
    }

    public function testAddInvalidSuccessHandler()
    {
        $this->setExpectedException(
            'fXmlRpc\Exception\InvalidArgumentException',
            'Expected parameter 3 to be of type "callable", "string" given'
        );

        $this->multicall->addCall('testMethod', array(), 'foo');
    }

    public function testAddInvalidErrorHandler()
    {
        $this->setExpectedException(
            'fXmlRpc\Exception\InvalidArgumentException',
            'Expected parameter 4 to be of type "callable", "string" given'
        );

        $this->multicall->addCall('testMethod', array(), null, 'foo');
    }

    public function testAddInvalidGlobalSuccessHandler()
    {
        $this->setExpectedException(
            'fXmlRpc\Exception\InvalidArgumentException',
            'Expected parameter 1 to be of type "callable", "NULL" given'
        );

        $this->multicall->onSuccess(null);
    }

    public function testAddInvalidGlobalErrorHandler()
    {
        $this->setExpectedException(
            'fXmlRpc\Exception\InvalidArgumentException',
            'Expected parameter 1 to be of type "callable", "NULL" given'
        );

        $this->multicall->onError(null);
    }

    public function testInvalidMethodType()
    {
        $this->setExpectedException(
            'fXmlRpc\Exception\InvalidArgumentException',
            'Expected parameter 1 to be of type "string", "object" of type "stdClass" given'
        );

        $this->multicall->addCall(new \stdClass());
    }
}
