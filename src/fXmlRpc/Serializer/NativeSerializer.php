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

use DateTime;
use fXmlRpc\Exception\MissingExtensionException;
use fXmlRpc\Exception\SerializationException;
use fXmlRpc\Value\Base64Interface;
use function base64_encode;
use function get_object_vars;
use function random_bytes;

final class NativeSerializer implements SerializerInterface
{
    private static $replacementTokens = [];

    public function __construct()
    {
        if (!extension_loaded('xmlrpc')) {
            throw MissingExtensionException::extensionMissing('xmlrpc');
        }
    }

    /** {@inheritdoc} */
    public function serialize($method, array $params = [])
    {
        $request = xmlrpc_encode_request(
            $method,
            self::convert($params),
            ['encoding' => 'UTF-8', 'escaping' => 'markup', 'verbosity' => 'no_white_space']
        );

        return str_replace('<string>' . self::getReplacementToken('struct') . '</string>', '<struct/>', $request);
    }

    private static function convert(array $params): array
    {
        foreach ($params as $key => $value) {
            $type = gettype($value);

            if ($type === 'array') {

                /** Find out if it is a struct or an array */
                $expectedIndex = 0;
                $isStruct = false;
                foreach ($value as $actualIndex => &$child) {
                    if ($expectedIndex !== $actualIndex) {
                        $isStruct = true;
                        break;
                    }
                    $expectedIndex++;
                }
                $value = self::convert($value);
                if ($isStruct) {
                    $type = 'object';
                    $value = (object) $value;
                } else {
                    $params[$key] = $value;
                }
            }

            if ($type === 'object') {
                if ($value instanceof DateTime) {
                    $params[$key] = (object) [
                        'xmlrpc_type' => 'datetime',
                        'scalar' => $value->format('Ymd\TH:i:s'),
                        'timestamp' => $value->format('u'),
                    ];

                } elseif ($value instanceof Base64Interface) {
                    $params[$key] = (object) [
                        'xmlrpc_type' => 'base64',
                        'scalar' => $value->getDecoded(),
                    ];

                } else {
                    $struct = [];
                    foreach (get_object_vars($value) as $structKey => $structValue) {
                        // Tricks ext/xmlrpc into always handling this as a struct
                        $struct[$structKey . "\0"] = $structValue;
                    }
                    $params[$key] = empty($struct) ? self::getReplacementToken('struct') : $struct;
                }
            } elseif ($type === 'resource') {
                throw SerializationException::invalidType($value);
            }
        }

        return $params;
    }

    private static function getReplacementToken(string $scope): string
    {
        return self::$replacementTokens[$scope] ?? (self::$replacementTokens[$scope] = bin2hex(random_bytes(12)));
    }
}
