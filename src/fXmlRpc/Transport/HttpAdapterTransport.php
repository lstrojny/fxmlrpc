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
use Http\Client\Exception as ClientException;
use Http\Client\Exception\HttpException as PsrHttpException;
use Http\Client\HttpClient;
use Http\Message\MessageFactory;

final class HttpAdapterTransport implements TransportInterface
{
    private $messageFactory;

    private $client;

    public function __construct(MessageFactory $messageFactory, HttpClient $client)
    {
        $this->client = $client;
        $this->messageFactory = $messageFactory;
    }

    /** {@inheritdoc} */
    public function send($endpoint, $payload)
    {
        try {
            $request = $this->messageFactory->createRequest(
                'POST',
                $endpoint,
                ['Content-Type' => 'text/xml; charset=UTF-8'],
                $payload
            );

            $response = $this->client->sendRequest($request);
            if ($response->getStatusCode() !== 200) {
                throw HttpException::httpError($response->getReasonPhrase(), $response->getStatusCode());
            }

            return (string) $response->getBody();

        } catch (PsrHttpException $e) {
            $response = $e->getResponse();
            throw HttpException::httpError($response->getReasonPhrase(), $response->getStatusCode());
        } catch (ClientException $e) {
            throw TransportException::transportError($e);
        }
    }
}
