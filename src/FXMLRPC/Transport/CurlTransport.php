<?php
/**
 * Copyright (C) 2012
 * cryptocompress <cryptocompress@googlemail.com>
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

namespace FXMLRPC\Transport;

use FXMLRPC\Exception\HttpException;
use FXMLRPC\Exception\TcpException;

class CurlTransport implements TransportInterface
{
    public function send($uri, $payload)
    {
        $handle = curl_init();

        curl_setopt($handle, CURLOPT_URL,               $uri);
        curl_setopt($handle, CURLOPT_HTTPHEADER,        array('Content-Type: text/xml'));
        curl_setopt($handle, CURLOPT_RETURNTRANSFER,    true);
        curl_setopt($handle, CURLOPT_HEADER,            true);
        curl_setopt($handle, CURLOPT_MAXREDIRS,         5);
        curl_setopt($handle, CURLOPT_TIMEOUT_MS,        5000);
        curl_setopt($handle, CURLOPT_CONNECTTIMEOUT_MS, 5000);
        curl_setopt($handle, CURLOPT_POST,              true);
        curl_setopt($handle, CURLOPT_POSTFIELDS,        $payload);

        $response = curl_exec($handle);
        if ($response === false || strlen($response) < 1) {
            throw new TcpException('Response was empty!' . "\n" . curl_error($handle), curl_errno($handle));
        }

        $body = substr($response, curl_getinfo($handle, CURLINFO_HEADER_SIZE));

        curl_close($handle);

        return $body;
    }
}
