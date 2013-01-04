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

namespace fXmlRpc;

use fXmlRpc\Transport\TransportInterface;
use fXmlRpc\Transport\StreamSocketTransport;
use fXmlRpc\Parser\ParserInterface;
use fXmlRpc\Parser\XmlReaderParser;
use fXmlRpc\Serializer\SerializerInterface;
use fXmlRpc\Serializer\XmlWriterSerializer;
use fXmlRpc\Exception\ResponseException;
use fXmlRpc\Exception\InvalidArgumentException;

final class Client implements ClientInterface
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var Transport\TransportInterface
     */
    private $transport;

    /**
     * @var Parser\ParserInterface
     */
    private $parser;

    /**
     * @var Serializer\SerializerInterface
     */
    private $serializer;

    /**
     * @var array
     */
    private $prependParams = array();

    /**
     * @var array
     */
    private $appendParams = array();

    /**
     * Create new client instance
     *
     * If no specific transport, parser or serializer is passed, default implementations
     * are used.
     *
     * @param string                         $uri
     * @param Transport\TransportInterface   $transport
     * @param Parser\ParserInterface         $parser
     * @param Serializer\SerializerInterface $serializer
     */
    public function __construct(
        $uri = null,
        TransportInterface $transport = null,
        ParserInterface $parser = null,
        SerializerInterface $serializer = null
    )
    {
        $this->uri = $uri;
        $this->transport = $transport ?: new StreamSocketTransport();
        $this->parser = $parser ?: new XmlReaderParser();
        $this->serializer = $serializer ?: new XmlWriterSerializer();
    }

    /**
     * {@inheritdoc}
     */
    public function setUri($uri)
    {
        if (!is_string($uri)) {
            throw InvalidArgumentException::expectedParameter(0, 'string', $uri);
        }

        $this->uri = $uri;
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * {@inheritdoc}
     */
    public function prependParams(array $params)
    {
        $this->prependParams = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getPrependParams()
    {
        return $this->prependParams;
    }

    /**
     * {@inheritdoc}
     */
    public function appendParams(array $params)
    {
        $this->appendParams = $params;
    }

    /**
     * {@inheritdoc}
     */
    public function getAppendParams()
    {
        return $this->appendParams;
    }

    /**
     * {@inheritdoc}
     * @throws Exception\ResponseException
     */
    public function call($methodName, array $params = array())
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(0, 'string', $methodName);
        }

        $params = array_merge($this->prependParams, $params, $this->appendParams);

        $response = $this->parser->parse(
            $this->transport->send($this->uri, $this->serializer->serialize($methodName, $params)),
            $isFault
        );

        if ($isFault) {
            throw ResponseException::fault($response);
        }

        return $response;
    }

    /**
     * {@inheritdoc}
     */
    public function multicall()
    {
        return new Multicall($this);
    }
}
