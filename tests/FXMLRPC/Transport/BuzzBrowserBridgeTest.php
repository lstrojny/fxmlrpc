<?php
namespace FXMLRPC\Transport;

class BuzzBrowserBridgeTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->browser = $this->getMockBuilder('Buzz\Browser')
                              ->setMethods(array('post'))
                              ->disableOriginalClone()
                              ->disableOriginalConstructor()
                              ->getMock();

        $this->response = $this->getMockBuilder('Buzz\Message\Response')
                               ->setMethods(array('getStatusCode', 'getReasonPhrase', 'getContent'))
                               ->disableOriginalClone()
                               ->disableOriginalConstructor()
                               ->getMock();

        $this->transport = new BuzzBrowserBridge($this->browser);
    }

    public function testExceptionIsThrownIfStatusCodeIsNot200()
    {
        $this->browser->expects($this->once())
                      ->method('post')
                      ->with('http://host', array(), 'REQUEST')
                      ->will($this->returnValue($this->response));

        $this->response->expects($this->at(0))
                       ->method('getStatusCode')
                       ->will($this->returnValue(404));

        $this->response->expects($this->at(1))
                       ->method('getReasonPhrase')
                       ->will($this->returnValue('Not Found'));

        $this->setExpectedException('RuntimeException', 'Not Found');
        $this->transport->send('http://host', 'REQUEST');
    }

    public function testSuccessfullRequest()
    {
        $this->browser->expects($this->once())
                      ->method('post')
                      ->with('http://host', array(), 'REQUEST')
                      ->will($this->returnValue($this->response));

        $this->response->expects($this->at(0))
                       ->method('getStatusCode')
                       ->will($this->returnValue(200));

        $this->response->expects($this->at(1))
                       ->method('getContent')
                       ->will($this->returnValue('RESPONSE'));

        $this->assertSame('RESPONSE', $this->transport->send('http://host', 'REQUEST'));
    }
}