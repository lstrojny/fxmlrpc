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

use DateTime;
use fXmlRpc\Value\Base64Interface;
use fXmlRpc\Exception\SerializationException;
use fXmlRpc\Exception\MissingExtensionException;

class NativeSerializer implements SerializerInterface
{
    public function __construct()
    {
        if (!extension_loaded('xmlrpc')) {
            throw MissingExtensionException::extensionMissing('xmlrpc');
        }
    }

    /**
     * {@inheritdoc}
     */
    public function serialize($method, array $params = array())
    {
        $toBeVisited = array(&$params);
        while (isset($toBeVisited[0]) && $value = &$toBeVisited[0]) {

            $type = gettype($value);
            if ($type === 'array') {
                foreach ($value as &$child) {
                    $toBeVisited[] = &$child;
                }

            } elseif ($type === 'object') {
                if ($value instanceof DateTime) {
                    $value = $value->format('Ymd\TH:i:s');
                    xmlrpc_set_type($value, 'datetime');
                } elseif ($value instanceof Base64Interface) {
                    $value = $value->getDecoded();
                    xmlrpc_set_type($value, 'base64');
                } else {
                    $value = get_object_vars($value);
                }
            } elseif ($type === 'resource') {
                throw SerializationException::invalidType($value);
            }

            array_shift($toBeVisited);
        }

        return xmlrpc_encode_request(
            $method,
            $params,
            array(
                'encoding'  => 'UTF-8',
                'escaping'  => 'markup',
                'verbosity' => 'no_white_space',
            )
        );
    }
}
