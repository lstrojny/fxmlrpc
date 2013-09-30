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

namespace fXmlRpc\Parser;

use DateTime;
use DateTimeZone;
use fXmlRpc\Value\Base64;

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
            array(Base64::deserialize('Zm9vYmFy'), 'base64', 'Zm9vYmFy', function($v){return $v->getDecoded();}),
            array('Ümläuts', 'string', '&#220;ml&#228;uts'),
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

        $isFault = true;
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

        $isFault = true;
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

        $isFault = true;
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

        $isFault = true;
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

        $isFault = true;
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

        $isFault = true;
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

        $isFault = true;
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

        $isFault = true;
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

        $isFault = true;
        $this->assertSame(' ', $this->parser->parse($xml, $isFault));
        $this->assertFalse($isFault);
    }

    public function testImplicitString()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodResponse>
                <params>
                    <param>
                    <value>STRING</value>
                    </param>
                </params>
                </methodResponse>';

        $isFault = true;
        $this->assertSame('STRING', $this->parser->parse($xml, $isFault));
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

        $isFault = false;
        $this->assertSame(
            array(
                'faultCode' => 123,
                'faultString' => 'ERROR',
            ),
            $this->parser->parse($xml, $isFault)
        );
        $this->assertTrue($isFault);
    }

    public function testNilValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <nil/>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $isFault = true;
        $this->assertSame(
            null,
            $this->parser->parse($xml, $isFault)
        );

        $this->assertFalse($isFault);
    }

    public function testApacheNilExtensionValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ext="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params>
                    <param>
                        <value>
                            <ext:nil/>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $this->assertSame(
            null,
            $this->parser->parse($xml, $isFault)
        );

        $this->assertFalse($isFault);
    }

    public function testParsingBase64WithNewlinesAsPythonXmlRpcEncodes()
    {
        $xml = "<?xml version='1.0'?>
        <methodResponse>
            <params>
                <param>
                    <value>
                        <base64>
                        SEVMTE8gV09STEQ=
                        </base64>
                    </value>
                </param>
            </params>
        </methodResponse>";

        $value = $this->parser->parse($xml, $isFault);
        $this->assertSame('HELLO WORLD', $value->getDecoded());
        $this->assertSame('SEVMTE8gV09STEQ=', $value->getEncoded());

        $this->assertFalse($isFault);
    }

    public function testParsingInvalidMultipleParams()
    {
        $xml = "<?xml version='1.0'?>
        <methodResponse>
            <params>
                <param>
                    <value>p1</value>
                </param>
                <param>
                    <value>p2</value>
                </param>
                <param>
                    <value>p3</value>
                </param>
            </params>
        </methodResponse>";

        $value = $this->parser->parse($xml, $isFault);
        $this->assertSame('p3', $value);

        $this->assertFalse($isFault);
    }

    public function testEntities_PreDefined_Name()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param><value><string>&quot;&amp;&apos;&lt;&gt;</string></value></param>
                </params>
            </methodResponse>';

        $value = $this->parser->parse($xml, $isFault);
        $this->assertSame('"&\'<>', $value);
        $this->assertFalse($isFault);
    }

    public function testEntities_PreDefined_Value()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param><value><string>&#34;&#38;&#39;&#60;&#62;</string></value></param>
                </params>
            </methodResponse>';

        $value = $this->parser->parse($xml, $isFault);
        $this->assertSame('"&\'<>', $value);
        $this->assertFalse($isFault);
    }

    public function testEntities_UnicodeEntitiesNumeric()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <string>&#916;&#1049;&#1511;&#1605;&#3671;&#12354;&#21494;&#33865;&#47568;</string>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $value = $this->parser->parse($xml, $isFault);
        $this->assertSame('ΔЙקم๗あ叶葉말', $value);
        $this->assertFalse($isFault);
    }

    public function testEntities_UnicodeEntitiesHex()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <string>&#x394;&#x419;&#x5E7;&#x645;&#xE57;&#x3042;&#x53F6;&#x8449;&#xB9D0;</string>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $value = $this->parser->parse($xml, $isFault);
        $this->assertSame('ΔЙקم๗あ叶葉말', $value);
        $this->assertFalse($isFault);
    }

    public function testXmlComments()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <!-- Comment -->
            <methodResponse>
                <!-- Comment -->
                <params>
                <!-- Comment -->
                    <param>
                    <!-- Comment -->
                        <value>
                        <!-- Comment -->
                            <string>value</string>
                        <!-- Comment -->
                        </value>
                    <!-- Comment -->
                    </param>
                <!-- Comment -->
                </params>
            <!-- Comment -->
            </methodResponse>
            <!-- Comment -->
        ';

        $value = $this->parser->parse($xml, $isFault);
        $this->assertSame('value', $value);
        $this->assertFalse($isFault);
    }

    public function testXxeAttack_1()
    {
        $xml = '<?xml version="1.0" encoding="ISO-8859-7"?>
            <!DOCTYPE foo [<!ENTITY xxefca0a SYSTEM "file:///etc/passwd">]>
            <methodResponse>
                <params>
                    <param><value>&xxefca0a;</value></param>
                </params>
            </methodResponse>';

        $value = $this->parser->parse($xml, $isFault);
        $this->assertFalse($isFault);
        $this->assertSame('', $value);
    }

    public function testEmptyArray_1()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <array>
                                <data/>
                            </array>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $isFault = true;
        $result = $this->parser->parse($string, $isFault);
        $this->assertFalse($isFault);
        $this->assertSame(array(), $result);
    }

    public function testEmptyStruct_1()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <struct>
                            </struct>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $isFault = true;
        $this->assertSame(array(), $this->parser->parse($string, $isFault));
        $this->assertFalse($isFault);
    }

    public function testEmptyStruct_2()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <struct/>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $isFault = true;
        $this->assertSame(array(), $this->parser->parse($string, $isFault));
        $this->assertFalse($isFault);
    }
}
