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

namespace fXmlRpc\Serializer;

use XMLWriter;
use Closure;
use DateTime;
use fXmlRpc\Value\Base64Interface;
use fXmlRpc\ExtensionSupportInterface;
use fXmlRpc\Exception\SerializationException;
use fXmlRpc\Exception\MissingExtensionException;

class XmlWriterSerializer implements SerializerInterface, ExtensionSupportInterface
{
    /**
     * @var XMLWriter
     */
    private $writer;

    /**
     * @var array
     */
    private $extensions = array();

    public function __construct()
    {
        if (!extension_loaded('xmlwriter')) {
            throw MissingExtensionException::extensionMissing('xmlwriter');
        }

        $this->writer = new XMLWriter();
        $this->writer->openMemory();
    }

    /**
     * {@inheritdoc}
     */
    public function enableExtension($extension)
    {
        $this->extensions[$extension] = true;
    }

    /**
     * {@inheritdoc}
     */
    public function disableExtension($extension)
    {
        $this->extensions[$extension] = false;
    }

    /**
     * {@inheritdoc}
     */
    public function isExtensionEnabled($extension)
    {
        return isset($this->extensions[$extension]) ? $this->extensions[$extension] : true;
    }

    /**
     * {@inheritdoc}
     */
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
        $paramNode = function() use ($writer) {
            $writer->startElement('param');
        };

        $toBeVisited = array();
        foreach (array_reverse($params) as $param) {
            $toBeVisited[] = $endNode;
            $toBeVisited[] = $param;
            $toBeVisited[] = $paramNode;
        }

        $nilTagName = $this->isExtensionEnabled(ExtensionSupportInterface::EXTENSION_NIL) ? 'nil' : 'string';

        while ($toBeVisited) {
            $node = array_pop($toBeVisited);
            $type = gettype($node);

            if ($type === 'string') {
                $writer->startElement('value');
                $writer->writeElement('string', $node);
                $writer->endElement();

            } elseif ($type === 'integer') {
                $writer->startElement('value');
                $writer->writeElement('int', $node);
                $writer->endElement();

            } elseif ($type === 'double') {
                if (!isset($precision)) {
                    $precision = ini_get('precision');
                }

                $writer->startElement('value');
                $writer->writeElement('double', $node);
                $writer->endElement();

            } elseif ($type === 'boolean') {
                $writer->startElement('value');
                $writer->writeElement('boolean', $node ? '1' : '0');
                $writer->endElement();

            } elseif ($type === 'NULL') {
                $writer->startElement('value');
                $writer->writeElement($nilTagName);
                $writer->endElement();

            } elseif ($type === 'array') {
                /** Find out if it is a struct or an array */
                $min = 0;
                foreach ($node as $min => &$child) break;
                $isStruct = false;
                $length = count($node) + $min;
                for ($a = $min; $a < $length; ++$a) {
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
                    foreach (array_reverse($node, true) as $key => $value) {
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

            } elseif ($type === 'object') {

                if ($node instanceof Closure) {
                    $node();

                } elseif ($node instanceof DateTime) {
                    $writer->startElement('value');
                    $writer->writeElement('dateTime.iso8601', $node->format('Ymd\TH:i:s'));
                    $writer->endElement();

                } elseif ($node instanceof Base64Interface) {
                    $writer->startElement('value');
                    $writer->writeElement('base64', $node->getEncoded() . "\n");
                    $writer->endElement();

                } else {
                    $node = get_object_vars($node);
                    goto struct;
                }
            } elseif ($type === 'resource') {
                throw SerializationException::invalidType($node);
            }
        }

        $writer->endElement();
        $writer->endElement();

        return $writer->flush(true);
    }
}
