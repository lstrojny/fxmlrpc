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

namespace FXMLRPC;

use FXMLRPC\Transport\TransportInterface;
use FXMLRPC\Transport\StreamSocketTransport;
use FXMLRPC\Parser\ParserInterface;
use FXMLRPC\Parser\XMLReaderParser;
use FXMLRPC\Serializer\SerializerInterface;
use FXMLRPC\Serializer\XMLWriterSerializer;
use FXMLRPC\Exception\ResponseException;

class Client
{
    protected $uri;

    protected $transport;

    protected $parser;

    protected $serializer;

    public function __construct(
        $uri = null,
        TransportInterface $transport = null,
        ParserInterface $parser = null,
        SerializerInterface $serializer = null
    )
    {
        $this->uri = $uri;
        $this->transport = $transport ?: new StreamSocketTransport();
        $this->parser = $parser ?: new XMLReaderParser();
        $this->serializer = $serializer ?: new XMLWriterSerializer();
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function call($method, array $params = array())
    {
        $requestPayload = $this->serializer->serialize($method, $params);
        $responsePayload = $this->transport->send($this->uri, $requestPayload);
        $response = $this->parser->parse($responsePayload, $isFault);

        if ($isFault) {
            throw new ResponseException(
                isset($response['faultString']) ? $response['faultString'] : 'Unknown',
                isset($response['faultCode']) ? $response['faultCode'] : 0
            );
        }

        return $response;
    }
}