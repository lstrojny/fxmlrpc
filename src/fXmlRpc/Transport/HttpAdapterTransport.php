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
namespace fXmlRpc\Transport;

use fXmlRpc\Exception\HttpException;
use fXmlRpc\Exception\TransportException;
use Psr\Http\Client\ClientExceptionInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;

final class HttpAdapterTransport implements TransportInterface
{
    private $messageFactory;

    private $streamFactory;

    private $client;

    public function __construct(
        RequestFactoryInterface $messageFactory,
        StreamFactoryInterface $streamFactory,
        ClientInterface $client
    ) {
        $this->client = $client;
        $this->messageFactory = $messageFactory;
        $this->streamFactory = $streamFactory;
    }

    /** {@inheritdoc} */
    public function send($endpoint, $payload)
    {
        try {
            $request = $this->messageFactory->createRequest('POST', $endpoint)
                ->withHeader('Content-Type', 'text/xml; charset=UTF-8')
                ->withBody($this->streamFactory->createStream($payload));

            $response = $this->client->sendRequest($request);
            if ($response->getStatusCode() !== 200) {
                throw HttpException::httpError($response->getReasonPhrase(), $response->getStatusCode());
            }

            return (string)$response->getBody();
        } catch (ClientExceptionInterface $e) {
            throw TransportException::transportError($e);
        }
    }
}
