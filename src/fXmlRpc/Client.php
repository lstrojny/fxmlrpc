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

use fXmlRpc\Exception\MissingDependencyException;
use fXmlRpc\Parser\ParserInterface;
use fXmlRpc\Parser\XmlReaderParser;
use fXmlRpc\Serializer\SerializerInterface;
use fXmlRpc\Serializer\XmlWriterSerializer;
use fXmlRpc\Exception\ResponseException;
use fXmlRpc\Exception\InvalidArgumentException;
use fXmlRpc\Transport\HttpAdapterTransport;
use fXmlRpc\Transport\TransportInterface;
use Ivory\HttpAdapter\HttpAdapterFactory;

final class Client implements ClientInterface
{
    /** @var string */
    private $uri;

    /** @var TransportInterface */
    private $transport;

    /** @var ParserInterface */
    private $parser;

    /** @var SerializerInterface */
    private $serializer;

    /** @var array */
    private $prependParams = [];

    /** @var array */
    private $appendParams = [];

    /**
     * Create new client instance
     *
     * If no specific transport, parser or serializer is passed, default implementations
     * are used.
     *
     * @param string $uri
     * @param TransportInterface $transport
     * @param ParserInterface $parser
     * @param SerializerInterface $serializer
     */
    public function __construct(
        $uri = null,
        TransportInterface $transport = null,
        ParserInterface $parser = null,
        SerializerInterface $serializer = null
    )
    {
        $this->uri = $uri;
        $this->transport = $transport ?: $this->getDefaultTransport();
        $this->parser = $parser ?: $this->getDefaultParser();
        $this->serializer = $serializer ?: $this->getDefaultSerializer();
    }

    /**
     * Set the endpoint URI
     *
     * @param string $uri
     */
    public function setUri($uri)
    {
        if (!is_string($uri)) {
            throw InvalidArgumentException::expectedParameter(0, 'string', $uri);
        }

        $this->uri = $uri;
    }

    /**
     * Return endpoint URI
     *
     * @return string
     */
    public function getUri()
    {
        return $this->uri;
    }

    /**
     * Prepend default parameters that should always be prepended
     *
     * @param array $params
     */
    public function prependParams(array $params)
    {
        $this->prependParams = $params;
    }

    /**
     * Get default parameters that are always prepended
     *
     * @return array
     */
    public function getPrependParams()
    {
        return $this->prependParams;
    }

    /**
     * Append default parameters that should always be prepended
     *
     * @param array $params
     */
    public function appendParams(array $params)
    {
        $this->appendParams = $params;
    }

    /**
     * Get default parameters that are always appended
     *
     * @return array
     */
    public function getAppendParams()
    {
        return $this->appendParams;
    }

    /** {@inheritdoc} */
    public function call($methodName, array $params = [])
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(0, 'string', $methodName);
        }

        $params = array_merge($this->prependParams, $params, $this->appendParams);
        $payload = $this->serializer->serialize($methodName, $params);
        $response = $this->transport->send($this->uri, $payload);
        $result = $this->parser->parse($response, $isFault);

        if ($isFault) {
            throw ResponseException::fault($result);
        }

        return $result;
    }

    /** {@inheritdoc} */
    public function multicall()
    {
        return new MulticallBuilder($this);
    }

    /** @return TransportInterface */
    private function getDefaultTransport()
    {
        if (!class_exists('Ivory\HttpAdapter\HttpAdapterFactory')) {
            throw MissingDependencyException::composerPackageMissing('egeloen/http-adapter');
        }

        return new HttpAdapterTransport(HttpAdapterFactory::guess());
    }

    /** @return ParserInterface */
    private function getDefaultParser()
    {
        return new XmlReaderParser();
    }

    /** SerializerInterface */
    private function getDefaultSerializer()
    {
        return new XmlWriterSerializer();
    }
}
