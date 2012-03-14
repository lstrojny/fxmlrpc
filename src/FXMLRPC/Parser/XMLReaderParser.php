<?php
/**
 * Copyright (C) 2012
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

namespace FXMLRPC\Parser;

use FXMLRPC\Value\Base64;
use XMLReader;
use RuntimeException;
use DateTime;
use DateTimeZone;

class XMLReaderParser implements ParserInterface
{
    public function __construct()
    {
        if (!extension_loaded('xmlreader')) {
            throw new RuntimeException('PHP extension ext/xmlreader missing');
        }
    }

    public function parse($xmlString, &$isFault)
    {
        $useErrors = libxml_use_internal_errors(true);

        $xml = new XMLReader();
        $xml->xml(
            $xmlString,
            'UTF-8',
            LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NOCDATA | LIBXML_NOEMPTYTAG | LIBXML_NOBLANKS
        );
        $xml->setParserProperty(XMLReader::VALIDATE, false);
        $xml->setParserProperty(XMLReader::LOADDTD, false);

        $aggregates = array();
        $depth = 0;
        $nextElements = array('methodResponse' => 1);
        while ($xml->read()) {
            $nodeType = $xml->nodeType;
            if ($nodeType === XMLReader::SIGNIFICANT_WHITESPACE && !isset($nextElements['#text'])) {
                continue;
            }

            $tagName = $xml->localName;
            if (!isset($nextElements[$tagName])) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid XML. Expected one of "%s", got "%s" on depth %d (context: "%s")',
                        join('", "', array_keys($nextElements)),
                        $tagName,
                        $xml->depth,
                        $xml->readOuterXml()
                    )
                );
            }

            switch ($nodeType) {
                case XMLReader::ELEMENT:
                    switch ($tagName) {
                        case 'methodResponse':
                            $nextElements = array('params' => 1, 'fault' => 1);
                            break;

                        case 'fault':
                            $nextElements = array('value' => 1);
                            $isFault = true;
                            break;

                        case 'params':
                            $nextElements = array('param' => 1);
                            $aggregates[$depth] = array();
                            $isFault = false;
                            break;

                        case 'param':
                            $nextElements = array('value' => 1);
                            break;

                        case 'array':
                            $nextElements = array('data' => 1);
                            ++$depth;
                            $aggregates[$depth] = array();
                            break;

                        case 'data':
                            $nextElements = array('value' => 1, 'data' => 1);
                            break;

                        case 'struct':
                            $nextElements = array('member' => 1);
                            ++$depth;
                            $aggregates[$depth] = array();
                            break;

                        case 'member':
                            $nextElements = array('name' => 1, 'value' => 1);
                            ++$depth;
                            $aggregates[$depth] = array();
                            break;

                        case 'name':
                            $nextElements = array('#text' => 1);
                            $type = 'name';
                            break;

                        case 'value':
                            $nextElements = array(
                                'string'           => 1,
                                'array'            => 1,
                                'struct'           => 1,
                                'int'              => 1,
                                'biginteger'       => 1,
                                'i8'               => 1,
                                'i4'               => 1,
                                'i2'               => 1,
                                'i1'               => 1,
                                'boolean'          => 1,
                                'double'           => 1,
                                'float'            => 1,
                                'bigdecimal'       => 1,
                                'dateTime.iso8601' => 1,
                                'base64'           => 1,
                                'nil'              => 1,
                            );
                            break;

                        case 'base64':
                        case 'string':
                        case 'biginteger':
                        case 'i8':
                        case 'dateTime.iso8601':
                            $nextElements = array('#text' => 1, $tagName => 1, 'value' => 1);
                            $type = $tagName;
                            $aggregates[$depth + 1] = '';
                            break;

                        case 'nil':
                            $nextElements = array($tagName => 1, 'value' => 1);
                            $type = $tagName;
                            $aggregates[$depth + 1] = null;
                            break;

                        case 'int':
                        case 'i4':
                        case 'i2':
                        case 'i1':
                            $nextElements = array('#text' => 1, $tagName => 1, 'value' => 1);
                            $type = $tagName;
                            $aggregates[$depth + 1] = 0;
                            break;

                        case 'boolean':
                            $nextElements = array('#text' => 1, $tagName => 1, 'value' => 1);
                            $type = $tagName;
                            $aggregates[$depth + 1] = false;
                            break;

                        case 'double':
                        case 'float':
                        case 'bigdecimal':
                            $nextElements = array('#text' => 1, $tagName => 1, 'value' => 1);
                            $type = $tagName;
                            $aggregates[$depth + 1] = 0.0;
                            break;

                        default:
                            throw new RuntimeException(
                                sprintf(
                                    'Invalid tag <%s> found',
                                    $tagName
                                )
                            );
                    }
                    break;

                case XMLReader::END_ELEMENT:
                    switch ($tagName) {
                        case 'param':
                        case 'fault':
                            break 3;
                            break;

                        case 'value':
                            $nextElements = array(
                                'param'  => 1,
                                'value'  => 1,
                                'data'   => 1,
                                'member' => 1,
                                'name'   => 1,
                                'int'    => 1,
                                'i4'     => 1,
                                'i2'     => 1,
                                'i1'     => 1,
                                'base64' => 1,
                                'fault'  => 1,
                            );
                            $aggregates[$depth][] = $aggregates[$depth + 1];
                            break;

                        case 'string':
                        case 'int':
                        case 'biginteger':
                        case 'i8':
                        case 'i4':
                        case 'i2':
                        case 'i1':
                        case 'boolean':
                        case 'double':
                        case 'float':
                        case 'bigdecimal':
                        case 'dateTime.iso8601':
                        case 'base64':
                            $nextElements = array('value' => 1);
                            break;

                        case 'data':
                            $nextElements = array('array' => 1);
                            break;

                        case 'array':
                            $nextElements = array('value' => 1);
                            --$depth;
                            break;

                        case 'name':
                            $nextElements = array('value' => 1, 'member' => 1);
                            $aggregates[$depth]['name'] = $aggregates[$depth + 1];
                            break;

                        case 'member':
                            $nextElements = array('struct' => 1, 'member' => 1);
                            $aggregates[$depth - 1][$aggregates[$depth]['name']] = $aggregates[$depth][0];
                            unset($aggregates[$depth], $aggregates[$depth + 1]);
                            --$depth;
                            break;

                        case 'struct':
                            $nextElements = array('value' => 1);
                            --$depth;
                            break;

                        default:
                            throw new RuntimeException(
                                sprintf(
                                    'Invalid tag </%s> found',
                                    $tagName
                                )
                            );
                    }
                    break;

                case XMLReader::TEXT:
                case XMLReader::SIGNIFICANT_WHITESPACE:
                    switch ($type) {
                        case 'int':
                        case 'i4':
                        case 'i2':
                        case 'i1':
                            $value = (int) $xml->value;
                            break;

                        case 'boolean':
                            $value = $xml->value === '1';
                            break;

                        case 'double':
                        case 'float':
                        case 'bigdecimal':
                            $value = (double) $xml->value;
                            break;

                        case 'dateTime.iso8601':
                            $value = DateTime::createFromFormat('Ymd\TH:i:s', $xml->value, new DateTimeZone('UTC'));
                            break;

                        case 'base64':
                            $value = new Base64($xml->value, true);
                            break;

                        default:
                            $value = $xml->value;
                            break;
                    }

                    $aggregates[$depth + 1] = $value;
                    $nextElements = array($type => 1);
                    break;
            }
        }

        libxml_use_internal_errors($useErrors);

        return isset($aggregates[0][0]) ? $aggregates[0][0] : null;
    }
}
