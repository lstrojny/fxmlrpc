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
namespace fXmlRpc;

class Proxy
{
    /** @var ClientInterface */
    private $client;

    /** @var string */
    private $namespace;

    /** @var string */
    private $namespaceSeparator = '.';

    /** @var Proxy[string] */
    private $proxies = [];

    /**
     * @param ClientInterface $client
     * @param string          $namespaceSeparator
     * @param string          $namespace
     */
    public function __construct(ClientInterface $client, $namespaceSeparator = '.', $namespace = null)
    {
        $this->client = $client;
        $this->namespaceSeparator = $namespaceSeparator;
        $this->namespace = $namespace;
    }

    /**
     * Invokes remote command
     *
     * @param  string $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, array $parameters)
    {
        return $this->client->call($this->prependNamespace($method), $parameters);
    }

    /**
     * Returns namespace specific Proxy instance
     *
     * @param  string $namespace
     * @return Proxy
     */
    public function __get($namespace)
    {
        $namespace = $this->prependNamespace($namespace);
        if (!isset($this->proxies[$namespace])) {
            $this->proxies[$namespace] = new static($this->client, $this->namespaceSeparator, $namespace);
        }

        return $this->proxies[$namespace];
    }

    /**
     * Prepend namespace if set
     *
     * @param  string $string
     * @return string
     */
    protected function prependNamespace($string)
    {
        if ($this->namespace === null) {
            return $string;
        }

        return $this->namespace . $this->namespaceSeparator . $string;
    }
}
