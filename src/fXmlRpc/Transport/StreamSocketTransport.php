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

namespace fXmlRpc\Transport;

use fXmlRpc\Exception\HttpException;
use fXmlRpc\Exception\TcpException;

class StreamSocketTransport implements TransportInterface
{
    /**
     * {@inheritdoc}
     */
    public function send($uri, $payload)
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => 'Content-Type: text/xml',
                    'content' => $payload,
                )
            )
        );

        $response = @file_get_contents($uri, false, $context);
        if ($response === false) {
            $error = error_get_last();

            if (strpos($error['message'], 'HTTP request failed')) {
                $matched = preg_match('|HTTP/1.[0-1]\s+(?<code>\d+)|', $error['message'], $matches);
                throw HttpException::httpError($error['message'], $matched ? $matches['code'] : null);
            }

            throw TcpException::transportError($error['message']);
        }

        return $response;
    }
}
