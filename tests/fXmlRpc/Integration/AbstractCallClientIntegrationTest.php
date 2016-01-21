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
namespace fXmlRpc\Integration;

use fXmlRpc;
use fXmlRpc\Client;
use fXmlRpc\CallClientInterface;

abstract class AbstractCallClientIntegrationTest extends AbstractIntegrationTest
{
    /** @dataProvider getClients */
    public function testNil(CallClientInterface $client)
    {
        $result = null;
        $this->assertSame($result, $client->call('system.echoNull', array($result)));
    }

    /** @dataProvider getClients */
    public function testArray(CallClientInterface $client)
    {
        $result = range(0, 10);
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testStruct(CallClientInterface $client)
    {
        $result = array('FOO' => 'BAR', 'BAZ' => 'BLA');
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testString(CallClientInterface $client)
    {
        $result = 'HELLO WORLD <> & ÜÖÄ';
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testBase64(CallClientInterface $client)
    {
        $expected = fXmlRpc\Value\Base64::serialize('HELLO WORLD');
        $result = $client->call('system.echo', array($expected));
        $this->assertSame($expected->getEncoded(), $result->getEncoded());
        $this->assertSame($expected->getDecoded(), $result->getDecoded());
    }

    /** @dataProvider getClients */
    public function testInteger(CallClientInterface $client)
    {
        $result = 100;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testNegativeInteger(CallClientInterface $client)
    {
        $result = -100;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testFloat(CallClientInterface $client)
    {
        $result = 100.12;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testNegativeFloat(CallClientInterface $client)
    {
        $result = -100.12;
        $this->assertSame($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testDate(CallClientInterface $client)
    {
        $result = new \DateTime('2011-01-12 23:12:10', new \DateTimeZone('UTC'));
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testComplexStruct(CallClientInterface $client)
    {
        $result = array(
            'el1' => array('one', 'two', 'three'),
            'el2' => array('first' => 'one', 'second' => 'two', 'third' => 'three'),
            'el3' => range(1, 100),
            'el4' => array(
                new \DateTime('2011-02-03 20:11:15', new \DateTimeZone('UTC')),
                new \DateTime('2011-02-03 20:11:15', new \DateTimeZone('UTC')),
            ),
            'el5' => 'str',
            'el6' => 1234,
            'el7' => -1234,
            'el8' => 1234.12434,
            'el9' => -1234.3245023,
        );
        $this->assertEquals($result, $client->call('system.echo', array($result)));
    }

    /** @dataProvider getClients */
    public function testFault(CallClientInterface $client)
    {
        try {
            $client->call('system.fault');
            $this->fail('Expected exception');
        } catch (fXmlRpc\Exception\FaultException $e) {
            $this->assertContains('ERROR', $e->getMessage());
            $this->assertContains('ERROR', $e->getFaultString());
            $this->assertSame(0, $e->getCode());
            $this->assertSame(123, $e->getFaultCode());
        }
    }

    protected function executeSystemFailureTest(Client $client)
    {
        $client->setUri(static::$errorEndpoint);

        try {
            $client->call('system.failure');
            $this->fail('Exception expected');
        } catch (\fXmlRpc\Exception\HttpException $e) {
            $this->assertInstanceOf('fXmlRpc\Exception\AbstractTransportException', $e);
            $this->assertInstanceOf('fXmlRpc\Exception\ExceptionInterface', $e);
            $this->assertInstanceOf('RuntimeException', $e);
            $this->assertStringStartsWith('An HTTP error occurred', $e->getMessage());
            $this->assertSame(500, $e->getCode());
        }
    }
}
