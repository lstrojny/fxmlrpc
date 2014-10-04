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

/**
 * Abstract base class for client decorators
 *
 * Extend this base class if you want to decorate functionality of the client
 */
abstract class AbstractDecorator implements ClientInterface
{
    /**
     * @var ClientInterface
     */
    protected $wrapped;

    /**
     * {@inheritdoc}
     */
    public function __construct(ClientInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    /**
     * {@inheritdoc}
     */
    public function setUri($uri)
    {
        return $this->wrapped->setUri($uri);
    }

    /**
     * {@inheritdoc}
     */
    public function getUri()
    {
        return $this->wrapped->getUri();
    }

    /**
     * {@inheritdoc}
     */
    public function setTransport(TransportInterface $transport)
    {
        return $this->wrapped->setTransport($transport);
    }

    /**
     * {@inheritdoc}
     */
    public function getTransport()
    {
        return $this->wrapped->getTransport();
    }

    /**
     * {@inheritdoc}
     */
    public function prependParams(array $params)
    {
        return $this->wrapped->prependParams($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getPrependParams()
    {
        return $this->wrapped->getPrependParams();
    }

    /**
     * {@inheritdoc}
     */
    public function appendParams(array $params)
    {
        return $this->wrapped->appendParams($params);
    }

    /**
     * {@inheritdoc}
     */
    public function getAppendParams()
    {
        return $this->wrapped->getAppendParams();
    }

    /**
     * {@inheritdoc}
     */
    public function call($methodName, array $arguments = [])
    {
        return $this->wrapped->call($methodName, $arguments);
    }

    /**
     * {@inheritdoc}
     */
    public function multicall()
    {
        return $this->wrapped->multicall();
    }
}
