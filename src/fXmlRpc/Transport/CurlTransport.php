<?php
/**
 * Copyright (C) 2012-2013
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

namespace fXmlRpc\Transport;

use fXmlRpc\Exception\HttpException;
use fXmlRpc\Exception\TcpException;

class CurlTransport implements TransportInterface
{
    /**
     * @var resource
     */
    protected $handle;

    public function __construct()
    {
        $this->handle = curl_init();

        curl_setopt_array(
            $this->handle,
            array(
                CURLOPT_HTTPHEADER        => array('Content-Type: text/xml'),
                CURLOPT_RETURNTRANSFER    => true,
                CURLOPT_HEADER            => true,
                CURLOPT_MAXREDIRS         => 5,
                CURLOPT_TIMEOUT_MS        => 5000,
                CURLOPT_CONNECTTIMEOUT_MS => 5000,
                CURLOPT_POST              => true,
            )
        );
    }

    public function __destruct()
    {
        if (is_resource($this->handle)) {
            curl_close($this->handle);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function send($uri, $payload)
    {
        curl_setopt($this->handle, CURLOPT_URL, $uri);
        curl_setopt($this->handle, CURLOPT_POSTFIELDS, $payload);

        $response = curl_exec($this->handle);
        if ($response === false || strlen($response) < 1) {
            throw TcpException::transportError(curl_error($this->handle));
        }

        $code = curl_getinfo($this->handle, CURLINFO_HTTP_CODE);
        if ($code !== 200) {
            throw HttpException::httpError(curl_error($this->handle), $code);
        }

        return substr($response, curl_getinfo($this->handle, CURLINFO_HEADER_SIZE));
    }
}
