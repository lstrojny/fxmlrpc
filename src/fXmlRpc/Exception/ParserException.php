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
namespace fXmlRpc\Exception;

final class ParserException extends RuntimeException
{
    public static function unexpectedTag($tagName, $elements, array $definedVariables, $depth, $xml)
    {
        $expectedElements = [];
        foreach ($definedVariables as $variableName => $variable) {
            if (substr($variableName, 0, 4) !== 'flag') {
                continue;
            }

            if (($elements & $variable) === $variable) {
                $expectedElements[] = substr($variableName, 4);
            }
        }

        return new static(
            sprintf(
                'Invalid XML. Expected one of "%s", got "%s" on depth %d (context: "%s")',
                implode('", "', $expectedElements),
                $tagName,
                $depth,
                $xml
            )
        );
    }

    public static function notXml($string)
    {
        return new static(sprintf('Invalid XML. Expected XML, string given: "%s"', $string));
    }
}
