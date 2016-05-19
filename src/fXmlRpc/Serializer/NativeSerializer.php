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

final class NativeSerializer implements SerializerInterface
{
    public function __construct()
    {
        if (!extension_loaded('xmlrpc')) {
            throw MissingExtensionException::extensionMissing('xmlrpc');
        }
    }

    /** {@inheritdoc} */
    public function serialize($method, array $params = [])
    {
        return xmlrpc_encode_request(
            $method,
            $this->convert($params),
            ['encoding' => 'UTF-8', 'escaping' => 'markup', 'verbosity' => 'no_white_space']
        );
    }

    private function convert(array $params)
    {
        foreach ($params as $key => $value) {
            $type = gettype($value);

            if ($type === 'array') {
                $params[$key] = $this->convert($value);

            } elseif ($type === 'object') {
                if ($value instanceof DateTime) {
                    $params[$key] = (object) [
                        'xmlrpc_type' => 'datetime',
                        'scalar'      => $value->format('Ymd\TH:i:s'),
                        'timestamp'   => $value->format('u'),
                    ];

                } elseif ($value instanceof Base64Interface) {
                    $params[$key] = (object) [
                        'xmlrpc_type' => 'base64',
                        'scalar'      => $value->getDecoded(),
                    ];

                } else {
                    $params[$key] = get_object_vars($value);
                }
            } elseif ($type === 'resource') {
                throw SerializationException::invalidType($value);
            }
        }

        return $params;
    }
}
