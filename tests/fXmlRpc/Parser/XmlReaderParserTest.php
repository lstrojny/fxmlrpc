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

namespace fXmlRpc\Parser;

class XmlReaderParserTest extends AbstractParserTest
{
    public function setUp()
    {
        if (!extension_loaded('xmlreader')) {
            $this->markTestSkipped('ext/xmlreader not available');
        }

        $this->parser = new XmlReaderParser();
    }

    public function createParserWithoutValidation()
    {
        return new XmlReaderParser(false);
    }

    public function testApacheI1ExtensionValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ext="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params>
                    <param>
                        <value>
                            <ext:i1>1</ext:i1>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $this->assertSame(1, $this->parser->parse($xml));
    }

    public function testApacheI2ExtensionValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ext="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params>
                    <param>
                        <value>
                            <ext:i2>1</ext:i2>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $this->assertSame(1, $this->parser->parse($xml));
    }

    public function testApacheI8ExtensionValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ext="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params>
                    <param>
                        <value>
                            <ext:i8>9223372036854775808</ext:i8>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $this->assertSame('9223372036854775808', $this->parser->parse($xml));
    }

    public function testApacheBigIntegerExtensionValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ext="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params>
                    <param>
                        <value>
                            <ext:biginteger>9223372036854775808</ext:biginteger>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $this->assertSame('9223372036854775808', $this->parser->parse($xml));
    }

    public function testApacheBigDecimalExtensionValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ext="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params>
                    <param>
                        <value>
                            <ext:bigdecimal>-100000000000000000.1234</ext:bigdecimal>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $this->assertSame(-100000000000000000.1234, $this->parser->parse($xml));
    }

    public function testApacheFloatExtensionValue()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ext="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params>
                    <param>
                        <value>
                            <ext:float>-100000000000000000.1234</ext:float>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $this->assertSame(-100000000000000000.1234, $this->parser->parse($xml));
    }

    public function testApacheDomExtension()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse xmlns:ex="http://ws.apache.org/xmlrpc/namespaces/extensions">
                <params><param><value><ex:dom><foo><bar>baz</bar></foo></ex:dom></value></param></params>
        </methodResponse>';

        $result = $this->parser->parse($xml);
        $this->assertInstanceOf('DOMDocument', $result);
        $this->assertXmlStringEqualsXmlString('<foo><bar>baz</bar></foo>', $result->saveXML());
    }

    public function testApacheDateTimeExtension()
    {
        $xml = '<?xml version="1.0" encoding="UTF-8"?>
        <methodResponse xmlns:ex="http://ws.apache.org/xmlrpc/namespaces/extensions">
            <params><param><value><ex:dateTime>2013-12-09T14:26:40.448+01:00</ex:dateTime></value></param></params>
        </methodResponse>';

        $result = $this->parser->parse($xml);
        $this->assertInstanceOf('DateTime', $result);
        $this->assertSame('2013-12-09T14:26:40.448000+01:00', $result->format('Y-m-d\TH:i:s.uP'));
    }

    public function testEmptyArray_2()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <array>
                            </array>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $result = $this->parser->parse($string);
        $this->assertSame(array(), $result);
    }

    public function testEmptyArray_3()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <array/>
                        </value>
                    </param>
                </params>
            </methodResponse>';

        $result = $this->parser->parse($string);
        $this->assertSame(array(), $result);
    }

    public function testInvalidXml()
    {
        $string = '<?xml version="1.0" encoding="UTF-8"?>
            <methodResponse>
                <invalidTag></invalidTag>
            </methodResponse>';

        $isFault = true;

        $this->setExpectedException(
            'fXmlRpc\Exception\RuntimeException',
            'Invalid XML. Expected one of "params", "fault", got "invalidTag" on depth 1 (context: "<invalidTag/>")'
        );
        $this->parser->parse($string);
    }
}
