<?php
/**
 * Copyright (C) 2012
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

namespace FXMLRPC\Transport;

use HttpRequest;
use HttpInvalidParamException;
use FXMLRPC\Exception\HttpException;
use FXMLRPC\Exception\TcpException;

class PeclHttpBridge implements TransportInterface
{
    private $request;

    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

    public function send($uri, $payload)
    {
        try {
            $this->request->setUrl($uri);
            $this->request->setMethod(HttpRequest::METH_POST);
            $this->request->setRawPostData($payload);
            $response = $this->request->send();

            if ($response->getResponseCode() !== 200) {
                throw new HttpException(
                    'An HTTP error occured: ' . $response->getResponseStatus(),
                    $response->getResponseCode()
                );
            }

            return $response->getBody();
        } catch (HttpInvalidParamException $e) {
            throw new TcpException('A transport error occured', null, $e);
        }
    }
}