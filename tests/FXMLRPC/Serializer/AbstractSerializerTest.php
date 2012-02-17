<?php
namespace FXMLRPC\Serializer;

use DateTime;
use DateTimeZone;

abstract class AbstractSerializerTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var SerializerInterface
     */
    protected $serializer;

    public function provideTypes()
    {
        return array(
            array('string', 'test string', 'test string'),
            array('int', 2, '2'),
            array('double', 1.2, '1.2'),
            array('boolean', true, '1'),
            array('boolean', false, '0'),
            array(
                'dateTime.iso8601',
                DateTime::createFromFormat('Y-m-d H:i:s', '1998-07-17 14:08:55', new DateTimeZone('UTC')),
                '19980717T14:08:55'
            ),
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
}