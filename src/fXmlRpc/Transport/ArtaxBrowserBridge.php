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

use Artax\Client;
use Artax\Request;
use Artax\SocketException;
use fXmlRpc\Exception\HttpException;
use fXmlRpc\Exception\TcpException;

final class ArtaxBrowserBridge extends AbstractHttpTransport
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send($uri, $payload)
    {
        $request = (new Request())
            ->setUri($uri)
            ->setProtocol('1.1')
            ->setMethod('POST')
            ->setBody($payload)
            ->setAllHeaders($this->getHeaders(true));

        try {
            $response = $this->client->request($request)->wait();
        } catch (SocketException $e) {
            throw TcpException::transportError($e);
        }

        if ($response->getStatus() !== 200) {
            throw HttpException::httpError('Invalid response code', $response->getStatus());
        }

        return $response->getBody();
    }
}
