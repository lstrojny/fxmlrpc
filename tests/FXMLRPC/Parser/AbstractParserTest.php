<?php
namespace FXMLRPC\Parser;

use DateTime;
use DateTimeZone;
use FXMLRPC\Value\Base64;

abstract class AbstractParserTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var ParserInterface
     */
    protected $parser;

    public static function provideSimpleTypes()
    {
        return array(
            array('Value', 'string', 'Value'),
            array('foo & bar', 'string', 'foo &amp; bar'),
            array('1 > 2', 'string', '1 &gt; 2'),
            array(12, 'i4', '12'),
            array(12, 'int', '12'),
            array(-4, 'int', ' -4 '),
            array(-4, 'i4', ' -4'),
            array(4, 'int', ' +4 '),
            array(4, 'i4', '  +4  '),
            array(4, 'i4', '000004'),
            array(false, 'boolean', '0'),
            array(true, 'boolean', '1'),
            array(1.2, 'double', '1.2'),
            array(1.2, 'double', '+1.2'),
            array(-1.2, 'double', '-1.2'),
            array(
                DateTime::createFromFormat('Y-m-d H:i:s', '1998-07-17 14:08:55', new DateTimeZone('UTC')),
                'dateTime.iso8601',
                '19980717T14:08:55'
            ),
            array(new Base64('Zm9vYmFy', true), 'base64', 'Zm9vYmFy', function($v){return $v->getDecoded();}),
        );
    }

    /**
     * @dataProvider provideSimpleTypes
     */
    public function testParsingSimpleTypes($expectedValue, $serializedType, $serializedValue, $callback = null)
    {
        $xml = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
                <methodResponse>
                <params>
                    <param>
                    <value><%1$s>%2$s</%1$s></value>
                    </param>
                </params>
                </methodResponse>',
            $serializedType,
            $serializedValue
        );

        $result = $this->parser->parse($xml, $isFault);
        if ($callback === null) {
            $this->assertEquals($expectedValue, $result);
        } else {
            $this->assertSame($callback($expectedValue), $callback($result));
        }
        $this->assertFalse($isFault);
    }

    /**
     * @dataProvider provideSimpleTypes
     */
    public function testEmptyTags($expectedValue, $serializedType)
    {
        $xml = sprintf(
            '<?xml version="1.0" encoding="UTF-8"?>
                <methodResponse>
                <params>
                    <param>
                    <value><%1$s></%1$s></value>
                    </param>
                </params>
                </methodResponse>',
            $serializedType
        );

        $this->assertEquals(null, $this->parser->parse($xml, $isFault));
        $this->assertFalse($isFault);
    }

    /**
     * @dataProvider provideSimpleTypes
     */
    public function testEmptyValue($expectedValue, $serializedType)
    {
        $xml = sprintf('<?xml version="1.0" encoding="UTF-8"?>
                <methodResponse>
                <params>
                    <param>
                        <value>
                            <%s/>
                        </value>
                    </param>
                </params>
                </methodResponse>',
            $serializedType
        );

        $this->assertEquals(null, $this->parser->parse($xml, $isFault));
        $this->assertFalse($isFault);

    }

    public function testParsingListResponse()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <array>
                                <data>
                                    <value><string>Str 0</string></value>
                                    <value><string>Str 1</string></value>
                                </data>
                            </array>
                        </value>
                    </param>
                </params>
            </methodResponse>';
        $result = $this->parser->parse($string, $isFault);
        $this->assertFalse($isFault);
        $this->assertSame(array('Str 0', 'Str 1'), $result);
        $this->assertSame('Str 0', current($result));
        $this->assertSame('Str 1', end($result));
    }


    public function testParsingNestedListResponse()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <array>
                                <data>
                                    <value>
                                        <array>
                                            <data>
                                                <value><string>Str 00</string></value>
                                                <value><string>Str 01</string></value>
                                            </data>
                                        </array>
                                    </value>
                                    <value>
                                        <array>
                                            <data>
                                                <value><string>Str 10</string></value>
                                                <value><string>Str 11</string></value>
                                            </data>
                                        </array>
                                    </value>
                                </data>
                            </array>
                        </value>
                    </param>
                </params>
            </methodResponse>';
        $this->assertSame(
            array(array('Str 00', 'Str 01'), array('Str 10', 'Str 11')),
            $this->parser->parse($string, $isFault)
        );
        $this->assertFalse($isFault);
    }

    public function testParsingStructs()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <struct>
                                <member>
                                    <name>FIRST</name>
                                    <value><string>ONE</string></value>
                                </member>
                                <member>
                                    <value><string>TWO</string></value>
                                    <name>SECOND</name>
                                </member>
                                <member>
                                    <name>THIRD</name>
                                    <value><string>THREE</string></value>
                                </member>
                            </struct>
                        </value>
                    </param>
                </params>
            </methodResponse>';
        $this->assertSame(
            array('FIRST' => 'ONE', 'SECOND' => 'TWO', 'THIRD' => 'THREE'),
            $this->parser->parse($string, $isFault)
        );
        $this->assertFalse($isFault);
    }

    public function testParsingStructsInStructs()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <struct>
                                <member>
                                    <name>FIRST</name>
                                    <value>
                                        <struct>
                                            <member>
                                                <name>ONE</name>
                                                <value><i4>1</i4></value>
                                            </member>
                                            <member>
                                                <name>TWO</name>
                                                <value><i4>2</i4></value>
                                            </member>
                                        </struct>
                                    </value>
                                </member>
                                <member>
                                    <name>SECOND</name>
                                    <value>
                                        <struct>
                                            <member>
                                                <name>ONE ONE</name>
                                                <value><i4>11</i4></value>
                                            </member>
                                            <member>
                                                <name>TWO TWO</name>
                                                <value><i4>22</i4></value>
                                            </member>
                                        </struct>
                                    </value>
                                </member>
                            </struct>
                        </value>
                    </param>
                </params>
            </methodResponse>';
        $this->assertSame(
            array(
                'FIRST' => array('ONE' => 1, 'TWO' => 2),
                'SECOND' => array('ONE ONE' => 11, 'TWO TWO' => 22),
            ),
            $this->parser->parse($string, $isFault)
        );
        $this->assertFalse($isFault);
    }

    public function testParsingListsInStructs()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <struct>
                                <member>
                                    <name>FIRST</name>
                                    <value>
                                        <array>
                                            <data>
                                                <value>
                                                    <array>
                                                        <data>
                                                            <value><string> Str 00</string></value>
                                                            <value><string> Str 01</string></value>
                                                        </data>
                                                    </array>
                                                </value>
                                                <value>
                                                    <array>
                                                        <data>
                                                            <value><string> Str 10</string></value>
                                                            <value><string> Str 11</string></value>
                                                        </data>
                                                    </array>
                                                </value>
                                            </data>
                                        </array>
                                    </value>
                                </member>
                                <member>
                                    <name>SECOND</name>
                                    <value>
                                        <array>
                                            <data>
                                                <value>
                                                    <array>
                                                        <data>
                                                            <value><string>Str 30</string></value>
                                                            <value><string>Str 31</string></value>
                                                        </data>
                                                    </array>
                                                </value>
                                                <value>
                                                    <array>
                                                        <data>
                                                            <value><string>Str 40</string></value>
                                                            <value><string>Str 41</string></value>
                                                        </data>
                                                    </array>
                                                </value>
                                            </data>
                                        </array>
                                    </value>
                                </member>
                            </struct>
                        </value>
                    </param>
                </params>
            </methodResponse>';
        $this->assertSame(
            array(
                'FIRST' => array(array(' Str 00', ' Str 01'), array(' Str 10', ' Str 11')),
                'SECOND' => array(array('Str 30', 'Str 31'), array('Str 40', 'Str 41')),
            ),
            $this->parser->parse($string, $isFault)
        );
        $this->assertFalse($isFault);
    }

    public function testEmptyString()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodResponse>
                <params>
                    <param>
                    <value><string> </string></value>
                    </param>
                </params>
                </methodResponse>';

        $this->assertSame(' ', $this->parser->parse($xml, $isFault));
        $this->assertFalse($isFault);
    }

    public function testParsingFaultCode()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <fault>
                    <value>
                        <struct>
                            <member>
                                <name>faultCode</name>
                                <value><int>123</int></value>
                            </member>
                            <member>
                                <name>faultString</name>
                                <value><string>ERROR</string></value>
                            </member>
                        </struct>
                    </value>
                </fault>
            </methodResponse>';

        $this->assertSame(
            array(
                'faultCode' => 123,
                'faultString' => 'ERROR',
            ),
            $this->parser->parse($xml, $isFault)
        );
        $this->assertTrue($isFault);
    }
}
