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

use DateTime;
use DateTimeZone;
use DOMDocument;
use fXmlRpc\Exception\FaultException;
use fXmlRpc\Exception\MissingExtensionException;
use fXmlRpc\Exception\ParserException;
use fXmlRpc\Value\Base64;
use XMLReader;

final class XmlReaderParser implements ParserInterface
{
    /**
     * @var bool
     */
    private $validateResponse;

    public function __construct($validateResponse = true)
    {
        if (!extension_loaded('xmlreader')) {
            throw MissingExtensionException::extensionMissing('xmlreader');
        }
        $this->validateResponse = $validateResponse;
    }

    /** {@inheritdoc} */
    public function parse($xmlString)
    {
        if ($this->validateResponse) {
            XmlChecker::validXml($xmlString);
        }

        $useErrors = libxml_use_internal_errors(true);

        $xml = new XMLReader();
        $xml->xml($xmlString, 'UTF-8', LIBXML_COMPACT | LIBXML_NOCDATA | LIBXML_NOBLANKS | LIBXML_PARSEHUGE);
        $xml->setParserProperty(XMLReader::VALIDATE, false);
        $xml->setParserProperty(XMLReader::LOADDTD, false);

// This following assignments are auto-generated using fXmlRpc\CodeGenerator\XmlReaderParserBitmaskGenerator
// Don’t edit manually
        static $flagmethodResponse = 0b000000000000000000000000001;
        static $flagparams = 0b000000000000000000000000010;
        static $flagfault = 0b000000000000000000000000100;
        static $flagparam = 0b000000000000000000000001000;
        static $flagvalue = 0b000000000000000000000010000;
        static $flagarray = 0b000000000000000000000100000;
        static $flagmember = 0b000000000000000000001000000;
        static $flagname = 0b000000000000000000010000000;
        ${'flag#text'} = 0b000000000000000000100000000;
        static $flagstring = 0b000000000000000001000000000;
        static $flagstruct = 0b000000000000000010000000000;
        static $flagint = 0b000000000000000100000000000;
        static $flagbiginteger = 0b000000000000001000000000000;
        static $flagi8 = 0b000000000000010000000000000;
        static $flagi4 = 0b000000000000100000000000000;
        static $flagi2 = 0b000000000001000000000000000;
        static $flagi1 = 0b000000000010000000000000000;
        static $flagboolean = 0b000000000100000000000000000;
        static $flagdouble = 0b000000001000000000000000000;
        static $flagfloat = 0b000000010000000000000000000;
        static $flagbigdecimal = 0b000000100000000000000000000;
        ${'flagdateTime.iso8601'} = 0b000001000000000000000000000;
        static $flagdateTime = 0b000010000000000000000000000;
        static $flagbase64 = 0b000100000000000000000000000;
        static $flagnil = 0b001000000000000000000000000;
        static $flagdom = 0b010000000000000000000000000;
        static $flagdata = 0b100000000000000000000000000;
// End of auto-generated code

        $aggregates = [];
        $depth = 0;
        $nextExpectedElements = 0b000000000000000000000000001;
        $i = 0;
        $isFault = false;
        while ($xml->read()) {
            $i++;
            $nodeType = $xml->nodeType;

            if (($nodeType === XMLReader::COMMENT || $nodeType === XMLReader::DOC_TYPE) ||
                (
                    $nodeType === XMLReader::SIGNIFICANT_WHITESPACE &&
                    ($nextExpectedElements & 0b000000000000000000100000000) !== 0b000000000000000000100000000)
            ) {
                continue;
            }

            if ($nodeType === XMLReader::ENTITY_REF) {
                libxml_use_internal_errors($useErrors);
                return '';
            }

            $tagName = $xml->localName;
            if ($nextExpectedElements !== null &&
                ($flag = isset(${'flag' . $tagName}) ? ${'flag' . $tagName} : -1) &&
                ($nextExpectedElements & $flag) !== $flag
            ) {
                libxml_use_internal_errors($useErrors);
                throw ParserException::unexpectedTag(
                    $tagName,
                    $nextExpectedElements,
                    get_defined_vars(),
                    $xml->depth,
                    $xml->readOuterXml()
                );
            }

            processing:
            switch ($nodeType) {
                case XMLReader::ELEMENT:
                    switch ($tagName) {
                        case 'methodResponse':
                            // Next: params, fault
                            $nextExpectedElements = 0b000000000000000000000000110;
                            break;

                        case 'params':
                            // Next: param
                            $nextExpectedElements = 0b000000000000000000000001000;
                            $aggregates[$depth] = [];
                            $isFault = false;
                            break;

                        case 'fault':
                            $isFault = true;
                        // Break intentionally omitted
                        case 'param':
                            // Next: value
                            $nextExpectedElements = 0b000000000000000000000010000;
                            break;

                        case 'array':
                            $aggregates[++$depth] = [];
                        // Break intentionally omitted
                        case 'data':
                            // Next: array, data, value
                            $nextExpectedElements = 0b100000000000000000000110000;
                            break;

                        case 'struct':
                            // Next: struct, member, value
                            $nextExpectedElements = 0b000000000000000010001010000;
                            $aggregates[++$depth] = [];
                            break;

                        case 'member':
                            // Next: name, value
                            $nextExpectedElements = 0b000000000000000000010010000;
                            $aggregates[++$depth] = [];
                            break;

                        case 'name':
                            // Next: #text
                            $nextExpectedElements = 0b000000000000000000100000000;
                            $type = 'name';
                            break;

                        case 'value':
                            $nextExpectedElements = 0b011111111111111111100110000;
                            $type = 'value';
                            $aggregates[$depth + 1] = '';
                            break;

                        case 'base64':
                        case 'string':
                        case 'biginteger':
                        case 'i8':
                        case 'dateTime.iso8601':
                        case 'dateTime':
                            // Next: value, $tagName, #text
                            $nextExpectedElements = 0b000000000000000000100010000 | ${'flag' . $tagName};
                            $type = $tagName;
                            $aggregates[$depth + 1] = '';
                            break;

                        case 'nil':
                            // Next: value, $tagName
                            $nextExpectedElements = 0b001000000000000000000010000 | ${'flag' . $tagName};
                            $type = $tagName;
                            $aggregates[$depth + 1] = null;
                            break;

                        case 'int':
                        case 'i4':
                        case 'i2':
                        case 'i1':
                            // Next: value, #text, $tagName
                            $nextExpectedElements = 0b000000000000000000100010000 | ${'flag' . $tagName};
                            $type = $tagName;
                            $aggregates[$depth + 1] = 0;
                            break;

                        case 'boolean':
                            // Next: value, #text, $tagName
                            $nextExpectedElements = 0b000000000000000000100010000 | ${'flag' . $tagName};
                            $type = 'boolean';
                            $aggregates[$depth + 1] = false;
                            break;

                        case 'double':
                        case 'float':
                        case 'bigdecimal':
                            // Next: value, #text, $tagName
                            $nextExpectedElements = 0b000000000000000000100010000 | ${'flag' . $tagName};
                            $type = $tagName;
                            $aggregates[$depth + 1] = 0.0;
                            break;

                        case 'dom':
                            $type = 'dom';
                            // Disable type checking
                            $nextExpectedElements = null;
                            $aggregates[$depth + 1] = $xml->readInnerXml();
                            break;
                    }
                    break;

                case XMLReader::END_ELEMENT:
                    switch ($tagName) {
                        case 'params':
                        case 'fault':
                            break 3;

                        case 'param':
                            // Next: params, param
                            $nextExpectedElements = 0b000000000000000000000001010;
                            break;

                        case 'value':
                            $nextExpectedElements = 0b100100000011100100011011100;
                            $aggregates[$depth][] = $aggregates[$depth + 1];
                            break;

                        case 'array':
                        case 'struct':
                            --$depth;
                        // Break intentionally omitted
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
                        case 'dateTime':
                        case 'base64':
                        case 'nil':
                            // Next: value
                            $nextExpectedElements = 0b000000000000000000000010000;
                            break;

                        case 'data':
                            // Next: array
                            $nextExpectedElements = 0b000000000000000000000100000;
                            break;

                        case 'name':
                            // Next: value, member
                            $nextExpectedElements = 0b000000000000000000001010000;
                            $aggregates[$depth]['name'] = $aggregates[$depth + 1];
                            break;

                        case 'member':
                            // Next: struct, member
                            $nextExpectedElements = 0b000000000000000010001000000;
                            $aggregates[$depth - 1][$aggregates[$depth]['name']] = $aggregates[$depth][0];
                            unset($aggregates[$depth], $aggregates[$depth + 1]);
                            --$depth;
                            break;
                    }
                    break;

                case XMLReader::TEXT:
                case XMLReader::SIGNIFICANT_WHITESPACE:
                    switch ($type) {
                        case 'int':
                        case 'i4':
                        case 'i2':
                        case 'i1':
                            $value = (int)$xml->value;
                            break;

                        case 'boolean':
                            $value = $xml->value === '1';
                            break;

                        case 'double':
                        case 'float':
                        case 'bigdecimal':
                            $value = (float)$xml->value;
                            break;

                        case 'dateTime.iso8601':
                            $value = DateTime::createFromFormat(
                                'Ymd\TH:i:s',
                                $xml->value,
                                isset($timezone) ? $timezone : $timezone = new DateTimeZone('UTC')
                            );
                            break;

                        case 'dateTime':
                            $value = DateTime::createFromFormat(
                                'Y-m-d\TH:i:s.uP',
                                $xml->value,
                                isset($timezone) ? $timezone : $timezone = new DateTimeZone('UTC')
                            );
                            break;

                        case 'base64':
                            $value = Base64::deserialize($xml->value);
                            break;

                        case 'dom':
                            $doc = new DOMDocument('1.0', 'UTF-8');
                            $doc->loadXML($aggregates[$depth + 1]);
                            $value = $doc;
                            break;

                        default:
                            $value = &$xml->value;
                            break;
                    }

                    $aggregates[$depth + 1] = $value;
                    if ($nextExpectedElements === null) {
                        break;
                    }
                    // Next: any
                    $nextExpectedElements = 0b111111111111111111111111111;
                    break;
            }

            if ($xml->isEmptyElement && $nodeType !== XMLReader::END_ELEMENT) {
                $nodeType = XMLReader::END_ELEMENT;
                goto processing;
            }
        }

        libxml_use_internal_errors($useErrors);

        $result = $aggregates ? array_pop($aggregates[0]) : null;

        if ($isFault) {
            throw FaultException::fault($result);
        }

        return $result;
    }
}
