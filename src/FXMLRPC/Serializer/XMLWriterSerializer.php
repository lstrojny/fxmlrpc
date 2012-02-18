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

namespace FXMLRPC\Serializer;

use XMLWriter;
use Closure;
use DateTime;
use stdClass;
use FXMLRPC\Value\Base64Interface;

class XMLWriterSerializer implements SerializerInterface
{
    private $writer;

    public function __construct()
    {
        if (!extension_loaded('xmlwriter')) {
            throw new RuntimeException('PHP extension ext/xmlwriter missing');
        }

        $this->writer = new XMLWriter();
        $this->writer->openMemory();
    }

    public function serialize($methodName, array $params = array())
    {
        $writer = $this->writer;

        $writer->startDocument('1.0', 'UTF-8');
        $writer->startElement('methodCall');
        $writer->writeElement('methodName', $methodName);
        $writer->startElement('params');

        $endNode = function() use ($writer) {
            $writer->endElement();
        };
        $valueNode = function() use ($writer) {
            $writer->startElement('value');
        };

        $toBeVisited = array_reverse($params);
        if ($toBeVisited) {
            $toBeVisited[] = function() use ($writer) {
                $writer->startElement('param');
            };
            array_unshift($toBeVisited, $endNode);
        }

        while ($toBeVisited) {
            $node = array_pop($toBeVisited);

            switch (gettype($node)) {
                case 'array':

                    /** Find out if it is a struct or an array */
                    $isStruct = false;
                    $length = count($node);
                    for ($a = 0; $a < $length; ++$a) {
                        if (!isset($node[$a])) {
                            $isStruct = true;
                            break;
                        }
                    }

                    if (!$isStruct) {
                        $toBeVisited[] = $endNode;
                        $toBeVisited[] = $endNode;
                        $toBeVisited[] = $endNode;
                        foreach (array_reverse($node) as $value) {
                            $toBeVisited[] = $value;
                        }
                        $toBeVisited[] = function() use ($writer) {
                            $writer->startElement('array');
                            $writer->startElement('data');
                        };
                        $toBeVisited[] = $valueNode;

                    } else {
                        struct:
                        $toBeVisited[] = $endNode;
                        $toBeVisited[] = $endNode;
                        foreach (array_reverse($node) as $key => $value) {
                            $toBeVisited[] = $endNode;
                            $toBeVisited[] = $value;
                            $toBeVisited[] = function() use ($writer, $key) {
                                $writer->writeElement('name', $key);
                            };
                            $toBeVisited[] = function() use ($writer) {
                                $writer->startElement('member');
                            };
                        }
                        $toBeVisited[] = function() use ($writer) {
                            $writer->startElement('struct');
                        };
                        $toBeVisited[] = $valueNode;
                    }
                    break;

                case 'object':
                    switch (true) {
                        case $node instanceof Closure:
                            $node();
                            break;

                        case $node instanceof DateTime:
                            $writer->startElement('value');
                            $writer->writeElement('dateTime.iso8601', $node->format('Ymd\TH:i:s'));
                            $writer->endElement();
                            break;

                        case $node instanceof Base64Interface:
                            $writer->startElement('value');
                            $writer->writeElement('base64', $node->getEncoded() . "\n");
                            $writer->endElement();
                            break;

                        default:
                            $node = get_object_vars($node);
                            goto struct;
                            break;
                    }
                    break;

                case 'string':
                    $writer->startElement('value');
                    $writer->writeElement('string', $node);
                    $writer->endElement();
                    break;

                case 'integer':
                    $writer->startElement('value');
                    $writer->writeElement('int', $node);
                    $writer->endElement();
                    break;

                case 'double':
                    $writer->startElement('value');
                    $writer->writeElement('double', $node);
                    $writer->endElement();
                    break;

                case 'boolean':
                    $writer->startElement('value');
                    $writer->writeElement('boolean', $node ? '1' : '0');
                    $writer->endElement();
                    break;
            }
        }

        $writer->endElement();
        $writer->endElement();

        return $writer->flush(true);
    }
}