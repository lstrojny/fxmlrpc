<?php
namespace FXMLRPC;

use XMLReader;
use RuntimeException;

class Parser
{
    public function parse($string)
    {
        libxml_use_internal_errors(true);

        $reader = new XMLReader();
        $reader->xml($string, 'UTF-8', LIBXML_COMPACT | LIBXML_PARSEHUGE | LIBXML_NOCDATA | LIBXML_NOEMPTYTAG);
        $reader->setParserProperty(XMLReader::VALIDATE, true);
        $reader->setParserProperty(XMLReader::LOADDTD, false);



        $aggregates = array();
        $ignoreWhitespace = true;
        $depth = 0;
        $expected = array('methodResponse');
        while ($reader->read()) {
            if ($ignoreWhitespace && $reader->nodeType === XMLReader::SIGNIFICANT_WHITESPACE) {
                continue;
            }

            if (!in_array($reader->name, $expected)) {
                throw new RuntimeException(
                    sprintf(
                        'Invalid XML. Expected one of "%s", got %s',
                        join('", "', $expected),
                        $reader->name
                    )
                );
            }

            switch ($reader->nodeType) {
                case XMLReader::ELEMENT:
                    switch ($reader->name) {
                        case 'methodResponse':
                            $expected = array('params');
                            break;

                        case 'params':
                            $expected = array('param');
                            $aggregates[$depth] = array();
                            break;

                        case 'param':
                            $expected = array('value');
                            break;

                        case 'value':
                            $expected = array('string', 'array', 'struct');
                            break;

                        case 'string':
                            $expected = array('#text');
                            $ignoreWhitespace = true;
                            $type = 'string';
                            break;

                        case 'array':
                            $expected = array('data');
                            ++$depth;
                            $aggregates[$depth] = array();
                            break;

                        case 'data':
                            $expected = array('value');
                            break;

                        case 'struct':
                            $expected = array('member');
                            ++$depth;
                            $aggregates[$depth] = array();
                            break;

                        case 'member':
                            $expected = array('name', 'value');
                            ++$depth;
                            $aggregates[$depth] = array();
                            break;

                        case 'name':
                            $expected = array('#text');
                            $type = 'name';
                            break;

                        default:
                            throw new RuntimeException(
                                sprintf(
                                    'Invalid tag <%s> found',
                                    $reader->name
                                )
                            );
                    }
                    break;

                case XMLReader::END_ELEMENT:
                    switch ($reader->name) {
                        case 'methodResponse':
                            $expected = array();
                            break;

                        case 'params':
                            $expected = array('methodResponse');
                            --$depth;
                            break;

                        case 'param':
                            $expected = array('params', 'param');
                            break;

                        case 'value':
                            $expected = array('param', 'value', 'data', 'member', 'name');
                            $aggregates[$depth][] = $aggregates[$depth + 1];
                            break;

                        case 'string':
                            $expected = array('value');
                            $ignoreWhitespace = true;
                            break;

                        case 'data':
                            $expected = array('array');
                            break;

                        case 'array':
                            $expected = array('value');
                            --$depth;
                            break;

                        case 'name':
                            $expected = array('value', 'member');
                            $aggregates[$depth]['name'] = $aggregates[$depth + 1];
                            break;

                        case 'member':
                            $expected = array('struct', 'member');
                            $aggregates[$depth - 1][$aggregates[$depth]['name']] = $aggregates[$depth][0];
                            unset($aggregates[$depth], $aggregates[$depth + 1]);
                            --$depth;
                            break;

                        case 'struct':
                            $expected = array('value');
                            --$depth;
                            break;

                        default:
                            throw new RuntimeException(
                                sprintf(
                                    'Invalid tag </%s> found',
                                    $reader->name
                                )
                            );
                    }
                    break;

                case XMLReader::TEXT:
                    $aggregates[$depth + 1] = $reader->value;
                    $expected = array($type);
                    $ignoreWhitespace = true;
                    break;
            }
        }

        return isset($aggregates[0]) ? $aggregates[0] : null;
    }
}
