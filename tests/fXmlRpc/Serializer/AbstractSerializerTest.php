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

namespace fXmlRpc\Serializer;

use DateTime;
use DateTimeZone;
use fXmlRpc\Value\Base64;

abstract class AbstractSerializerTest extends \PHPUnit_Framework_TestCase
{
    /** @var SerializerInterface */
    protected $serializer;

    public function provideTypes()
    {
        return array(
            array('string', 'test string', 'test string'),
            array('int', 2, '2'),
            array('int', -2, '-2'),
            array('double', 1.2, '1.2'),
            array('double', -1.2, '-1.2'),
            array('boolean', true, '1'),
            array('boolean', false, '0'),
            array(
                'dateTime.iso8601',
                DateTime::createFromFormat('Y-m-d H:i:s', '1998-07-17 14:08:55', new DateTimeZone('UTC')),
                '19980717T14:08:55'
            ),
            array('base64', Base64::serialize('string'), "c3RyaW5n\n"),
            array('string', 'Ümläuts', '&#220;ml&#228;uts'),
        );
    }

    /**
     * @dataProvider provideTypes
     */
    public function testSerializingMethodCallWithSimpleArgument($type, $expectedValue, $xmlValue)
    {
        $xml = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <%1$s>%2$s</%1$s>
                            </value>
                        </param>
                    </params>
                </methodCall>',
            $type,
            $xmlValue
        );
        $this->assertXmlStringEqualsXmlString($xml, $this->serializer->serialize('method', array($expectedValue)));
    }

    /**
     * @dataProvider provideTypes
     */
    public function testSerializingMethodCallWithComplexArguments($type, $expectedValue, $xmlValue)
    {
        $xml = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <array>
                                    <data>
                                        <value><%1$s>%2$s</%1$s></value>
                                        <value><%1$s>%2$s</%1$s></value>
                                        <value><%1$s>%2$s</%1$s></value>
                                    </data>
                                </array>
                            </value>
                        </param>
                    </params>
                </methodCall>',
            $type,
            $xmlValue
        );
        $this->assertXmlStringEqualsXmlString($xml, $this->serializer->serialize('method', array(array($expectedValue, $expectedValue, $expectedValue))));
    }

    public function testSerializingMethodCallWithoutArguments()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params/>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString($xml, $this->serializer->serialize('method'));
    }

    public function testSerializingMethodCallWithStringParameter()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <string> TESTSTR </string>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString($xml, $this->serializer->serialize('method', array(' TESTSTR ')));
    }

    public function testSerializingMethodCallWithMultipeStringParameters()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <string> TESTSTR1 </string>
                            </value>
                        </param>
                        <param>
                            <value>
                                <string> TESTSTR2 </string>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString($xml, $this->serializer->serialize('method', array(' TESTSTR1 ', ' TESTSTR2 ')));
    }

    public function testSerializingArrays()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <array>
                                    <data>
                                        <value><string>ONE</string></value>
                                        <value><string>TWO</string></value>
                                    </data>
                                </array>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString($xml, $this->serializer->serialize('method', array(array('ONE', 'TWO'))));
    }

    public function testSerializingArraysNotStartingWithZero()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <array>
                                    <data>
                                        <value><string>ONE</string></value>
                                        <value><string>TWO</string></value>
                                    </data>
                                </array>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array(array(1 => 'ONE', 2 => 'TWO')))
        );
    }

    public function testSerializingArraysNotStartingWithZeroWithGaps()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>1</name>
                                        <value><string>ONE</string></value>
                                    </member>
                                    <member>
                                        <name>3</name>
                                        <value><string>TWO</string></value>
                                    </member>
                                </struct>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array(array(1 => 'ONE', 3 => 'TWO')))
        );
    }

    public function testSerializingUnorderedArray()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>-1</name>
                                        <value><string>MINUS ONE</string></value>
                                    </member>
                                    <member>
                                        <name>-2</name>
                                        <value><string>MINUS TWO</string></value>
                                    </member>
                                    <member>
                                        <name>1</name>
                                        <value><string>ONE</string></value>
                                    </member>
                                </struct>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array(array(-1 => 'MINUS ONE', -2 => 'MINUS TWO', 1 => 'ONE')))
        );
    }

    public function testSerializingEmptyArray()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <array>
                                    <data/>
                                </array>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array(array()))
        );
    }

    public function testSerializingStructs()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>FIRST</name>
                                        <value><string>ONE</string></value>
                                    </member>
                                    <member>
                                        <name>SECOND</name>
                                        <value><string>TWO</string></value>
                                    </member>
                                    <member>
                                        <name>THIRD</name>
                                        <value><string>THREE</string></value>
                                    </member>
                                </struct>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array(array('FIRST' => 'ONE', 'SECOND' => 'TWO', 'THIRD' => 'THREE')))
        );
    }

    public function testSerializingObjects()
    {
    $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>FIRST</name>
                                        <value><string>ONE</string></value>
                                    </member>
                                    <member>
                                        <name>SECOND</name>
                                        <value><string>TWO</string></value>
                                    </member>
                                    <member>
                                        <name>THIRD</name>
                                        <value><string>THREE</string></value>
                                    </member>
                                </struct>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array((object) array('FIRST' => 'ONE', 'SECOND' => 'TWO', 'THIRD' => 'THREE')))
        );
    }

    public function testSerializingArraysInStructs()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>FIRST</name>
                                        <value>
                                            <array>
                                                <data>
                                                    <value><string>ONE</string></value>
                                                    <value><string>TWO</string></value>
                                                </data>
                                            </array>
                                        </value>
                                    </member>
                                    <member>
                                        <name>SECOND</name>
                                        <value><string>TWO</string></value>
                                    </member>
                                    <member>
                                        <name>THIRD</name>
                                        <value><string>THREE</string></value>
                                    </member>
                                </struct>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array(array('FIRST' => array('ONE', 'TWO'), 'SECOND' => 'TWO', 'THIRD' => 'THREE')))
        );
    }

    public function testXmlDeclaration()
    {
        $this->assertStringStartsWith('<?xml version="1.0" encoding="UTF-8"?>', $this->serializer->serialize('methodName'));
    }

    public function testSerializingStdClass()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>FOO</name>
                                        <value><string>BAR</string></value>
                                    </member>
                                </struct>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array((object) array('FOO' => 'BAR')))
        );
    }

    public function testSerializingOtherClasses()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <struct>
                                    <member>
                                        <name>publicProperty</name>
                                        <value><string>PUBLIC</string></value>
                                    </member>
                                </struct>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', array(new Test()))
        );
    }

    public function testSerializingResource()
    {
        $resource = stream_context_create();

        $this->setExpectedException(
            'fXmlRpc\Exception\SerializationException',
            'Could not serialize resource of type "stream-context"'
        );
        $this->serializer->serialize('method', array($resource));
    }

    public function testSerialeArray()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodCall>
                <methodName>method</methodName>
                <params>
                    <param>
                        <value>
                            <struct>
                                <member>
                                    <name>_FCGI_</name>
                                <value>
                                    <string>some value</string>
                                </value>
                            </member>
                        </struct>
                    </value>
                </param>
            </params>
        </methodCall>';
        $this->assertXmlStringEqualsXmlString(
            $xml,
            $this->serializer->serialize('method', [['_FCGI_' => 'some value']])
        );
    }
}

class Test
{
    public $publicProperty = 'PUBLIC';
    protected $protectedProperty = 'PROTECTED';
    private $privateProperty = 'PRIVATE';

    public static $publicStatic = 'PUBLIC STATIC';
    protected static $protectedStatic = 'PROTECTED STATIC';
    private static $privateStatic = 'PRIVATE STATIC';
}
