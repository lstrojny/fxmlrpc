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

use fXmlRpc\Exception\InvalidArgumentException;

class Multicall
{
    /**
     * @var ClientInterface
     */
    private $client;

    /**
     * @var int
     */
    private $index = 0;

    /**
     * @var array
     */
    private $calls = array();

    /**
     * @var array
     */
    private $handlers = array();

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * @param string $methodName
     * @param array $params
     * @param callable $handler
     * @return self
     */
    public function addCall($methodName, array $params = array(), $handler = null)
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(1, 'string', $methodName);
        }

        if ($handler !== null && !is_callable($handler)) {
            throw InvalidArgumentException::expectedParameter(3, 'callable', $handler);
        }

        $this->calls[$this->index] = array('methodName' => $methodName, 'params' => $params);
        $this->handlers[$this->index] = $handler;
        ++$this->index;

        return $this;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $results = $this->client->call('system.multicall', array($this->calls));

        foreach ($results as $index => $result) {
            if (!isset($this->handlers[$index])) {
                continue;
            }

            call_user_func($this->handlers[$index], $result);
        }

        return $results;
    }

    /**
     * @return ClientInterface
     */
    public function getClient()
    {
        return $this->client;
    }
}
