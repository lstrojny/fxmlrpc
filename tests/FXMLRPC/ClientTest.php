<?php
namespace FXMLRPC;

class ClientTest extends \PHPUnit_Framework_TestCase
{
    public function setUp()
    {
        $this->serializer = $this->getMockBuilder('FXMLRPC\Serializer\SerializerInterface')
                                 ->getMock();
        $this->parser = $this->getMockBuilder('FXMLRPC\Parser\ParserInterface')
                             ->getMock();
        $this->transport = $this->getMockBuilder('FXMLRPC\Transport\TransportInterface')
                             ->getMock();

        $this->client = new Client('http://foo.com', $this->transport, $this->parser, $this->serializer);
    }

    public function testCallSerializer()
    {
        $this->serializer->expects($this->once())
                         ->method('serialize')
                         ->with('methodName', array('p1', 'p2'))
                         ->will($this->returnValue('REQUEST'));
        $this->transport->expects($this->once())
                        ->method('send')
                        ->with('http://foo.com', 'REQUEST')
                        ->will($this->returnValue('RESPONSE'));
        $this->parser->expects($this->once())
                     ->method('parse')
                     ->with('RESPONSE')
                     ->will($this->returnValue('NATIVE VALUE'));

        $this->assertSame('NATIVE VALUE', $this->client->call('methodName', array('p1', 'p2')));
    }
}