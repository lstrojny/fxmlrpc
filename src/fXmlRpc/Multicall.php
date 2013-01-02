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
     * @var callable
     */
    private $onSuccess;

    /**
     * @var callable
     */
    private $onError;

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Add a call to the sequence
     *
     * @param string $methodName
     * @param array $params
     * @param callable $onSuccess
     * @param callable $onError
     * @return self
     */
    public function addCall($methodName, array $params = array(), $onSuccess = null, $onError = null)
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(1, 'string', $methodName);
        }

        if ($onSuccess !== null && !is_callable($onSuccess)) {
            throw InvalidArgumentException::expectedParameter(3, 'callable', $onSuccess);
        }

        if ($onError !== null && !is_callable($onError)) {
            throw InvalidArgumentException::expectedParameter(4, 'callable', $onError);
        }

        $this->calls[$this->index] = array('methodName' => $methodName, 'params' => $params);
        $this->handlers[$this->index] = array('onSuccess' => $onSuccess, 'onError' => $onError);
        ++$this->index;

        return $this;
    }

    /**
     * @param $onSuccess
     */
    public function onSuccess($onSuccess)
    {
        if (!is_callable($onSuccess)) {
            throw InvalidArgumentException::expectedParameter(1, 'callable', $onSuccess);
        }

        $this->onSuccess = $onSuccess;

        return $this;
    }

    public function onError($onError)
    {
        if (!is_callable($onError)) {
            throw InvalidArgumentException::expectedParameter(1, 'callable', $onError);
        }

        $this->onError = $onError;

        return $this;
    }

    /**
     * @return array
     */
    public function execute()
    {
        $results = $this->client->call('system.multicall', array($this->calls));

        foreach ($results as $index => $result) {

            $isError = is_array($result) && isset($result['faultCode']);

            if ($this->handlers[$index]['onSuccess'] !== null) {
                if (!$isError || $this->handlers[$index]['onError'] === null) {
                    call_user_func($this->handlers[$index]['onSuccess'], $result);
                } else {
                    call_user_func($this->handlers[$index]['onError'], $result);
                }
            }

            if ($isError && $this->onError !== null) {
                call_user_func($this->onError, $result);
            } elseif ($this->onSuccess !== null) {
                call_user_func($this->onSuccess, $result);
            }
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
