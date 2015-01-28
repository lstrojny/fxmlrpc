<?php
/**
 * Copyright (C) 2012-2015
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

use fXmlRpc\Parser\ParserInterface;
use fXmlRpc\Parser\XmlReaderParser;
use fXmlRpc\Serializer\SerializerInterface;
use fXmlRpc\Serializer\XmlWriterSerializer;
use fXmlRpc\Exception\HttpException;
use fXmlRpc\Exception\ResponseException;
use fXmlRpc\Exception\InvalidArgumentException;
use Ivory\HttpAdapter\HttpAdapterInterface;
use Ivory\HttpAdapter\HttpAdapterFactory;

final class Client implements ClientInterface
{
    /**
     * @var string
     */
    private $uri;

    /**
     * @var HttpAdapterInterface
     */
    private $httpAdapter;

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
    private $prependParams = [];

    /**
     * @var array
     */
    private $appendParams = [];

    /**
     * Create new client instance
     *
     * If no specific transport, parser or serializer is passed, default implementations
     * are used.
     *
     * @param string                         $uri
     * @param HttpAdapterInterface           $httpAdapter
     * @param Parser\ParserInterface         $parser
     * @param Serializer\SerializerInterface $serializer
     */
    public function __construct(
        $uri = null,
        HttpAdapterInterface $httpAdapter = null,
        ParserInterface $parser = null,
        SerializerInterface $serializer = null
    )
    {
        $this->uri = $uri;
        $this->httpAdapter = $httpAdapter ?: HttpAdapterFactory::guess();
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
    public function call($methodName, array $params = [])
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(0, 'string', $methodName);
        }

        $params = array_merge($this->prependParams, $params, $this->appendParams);

        // forcing it here
        // custom headers can be created using ivory's message factory
        $headers = [
            'Content-Type' => 'text/xml; charset=UTF-8'
        ];

        $response = $this->httpAdapter->post($this->uri, $headers, $this->serializer->serialize($methodName, $params));

        if ($response->getStatusCode() !== 200) {
            throw HttpException::httpError($response->getReasonPhrase(), $response->getStatusCode());
        }

        $response = $this->parser->parse((string) $response->getBody(), $isFault);

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
