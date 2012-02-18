<?php
namespace FXMLRPC\Serializer;

use XMLWriter;
use Closure;
use DateTime;
use stdClass;

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