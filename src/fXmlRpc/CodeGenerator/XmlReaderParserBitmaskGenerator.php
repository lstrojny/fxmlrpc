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

namespace fXmlRpc\CodeGenerator;

final class XmlReaderParserBitmaskGenerator
{
    private $basicTypes = [
        'methodResponse',
        'params',
        'fault',
        'param',
        'value',
        'array',
        'member',
        'name',
        '#text',
        'string',
        'struct',
        'int',
        'biginteger',
        'i8',
        'i4',
        'i2',
        'i1',
        'boolean',
        'double',
        'float',
        'bigdecimal',
        'dateTime.iso8601',
        'dateTime',
        'base64',
        'nil',
        'dom',
        'data',
    ];

    private $combinedTypes = [];

    private $typeCount = 0;

    private $values = [];

    public function __construct()
    {
        $this->combinedTypes = [
            'expectedForMethodResponse' => ['params', 'fault'],
            'expectedForMember' => ['name', 'value'],
            'expectedForSimpleType' => ['#text', 'value'],
            'expectedForNil' => ['nil', 'value'],
            'expectedForValue' => [
                'string',
                'array',
                'struct',
                'int',
                'biginteger',
                'i8',
                'i4',
                'i2',
                'i1',
                'boolean',
                'double',
                'float',
                'bigdecimal',
                'dateTime.iso8601',
                'dateTime',
                'base64',
                'nil',
                'dom',
                '#text',
                'value',
            ],
            'expectedForStruct' => ['member', 'struct', 'value'],
            'expectedForData' => ['data', 'value', 'array'],
            'expectedAfterValue' => [
                'param',
                'value',
                'data',
                'member',
                'name',
                'int',
                'i4',
                'i2',
                'i1',
                'base64',
                'fault'
            ],
            'expectedAfterParam' => ['param', 'params'],
            'expectedAfterName' => ['value', 'member'],
            'expectedAfterMember' => ['struct', 'member'],
            'allFlags' => $this->basicTypes,
        ];

        $this->typeCount = count($this->basicTypes);
    }

    private function createBitmaskVariable($type, $bitmask, $prefix = '')
    {
        $variableName = preg_match('/^\w+[\d\w_]*$/', $type)
            ? 'static $' . $prefix . $type
            : '${\'' . $prefix . $type . '\'}';
        $this->values[$type] = $bitmask;

        return $variableName . ' = 0b' . sprintf('%0' . $this->typeCount . 'b', $this->values[$type]) . ';';
    }

    public function generate()
    {
        $code = [];
        $bitmask = 1;
        foreach ($this->basicTypes as $type) {
            $code[] = $this->createBitmaskVariable($type, $bitmask, 'flag');
            $bitmask = $bitmask << 1;
        }

        foreach ($this->combinedTypes as $type => $combination) {
            $value = 0;
            foreach ($combination as $subType) {
                $value |= $this->values[$subType];
            }
            $code[] = $this->createBitmaskVariable($type, $value);
        }

        $commentStart = <<<'EOS'
// This following assignments are auto-generated using %s
// Don’t edit manually
EOS;

        $commentStart = sprintf($commentStart, __CLASS__);

        $commentEnd = '// End of auto-generated code';

        return $commentStart . "\n" . implode("\n", $code) . "\n" . $commentEnd;
    }
}
