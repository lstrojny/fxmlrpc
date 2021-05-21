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

use fXmlRpc\ExtensionSupportInterface;
use fXmlRpc\Value\Base64;

class XmlWriterSerializerTest extends AbstractSerializerTest
{
    protected function setUp(): void
    {
        $this->serializer = new XmlWriterSerializer();
    }

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
                \DateTime::createFromFormat('Y-m-d H:i:s', '1998-07-17 14:08:55', new \DateTimeZone('UTC')),
                '19980717T14:08:55'
            ),
            array('base64', Base64::serialize('string'), "c3RyaW5n\n"),
            array('string', 'Ümläuts', '&#220;ml&#228;uts'),
        );
    }

    public function testDisableNilExtension()
    {
        $this->assertInstanceOf('fXmlRpc\ExtensionSupportInterface', $this->serializer);
        $nilXml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <nil></nil>
                            </value>
                        </param>
                    </params>
                </methodCall>';

        $this->assertXmlStringEqualsXmlString($nilXml, $this->serializer->serialize('method', array(null)));
        $this->assertNull($this->serializer->disableExtension(ExtensionSupportInterface::EXTENSION_NIL));
        $stringXml = '<?xml version="1.0" encoding="UTF-8"?>
                <methodCall>
                    <methodName>method</methodName>
                    <params>
                        <param>
                            <value>
                                <string></string>
                            </value>
                        </param>
                    </params>
                </methodCall>';
        $this->assertXmlStringEqualsXmlString($stringXml, $this->serializer->serialize('method', array(null)));
    }
}
