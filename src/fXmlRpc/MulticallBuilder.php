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

use fXmlRpc\Exception\InvalidArgumentException;

final class MulticallBuilder implements MulticallBuilderInterface
{
    /** @var ClientInterface */
    private $client;

    /** @var integer */
    private $index = 0;

    /** @var array */
    private $calls = [];

    /** @var array */
    private $handlers = [];

    /** @var callable */
    private $onSuccess;

    /** @var callable */
    private $onError;

    /** @param ClientInterface $client */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /** {@inheritdoc} */
    public function addCall($methodName, array $params = [], callable $onSuccess = null, callable $onError = null)
    {
        if (!is_string($methodName)) {
            throw InvalidArgumentException::expectedParameter(1, 'string', $methodName);
        }

        $this->calls[$this->index] = compact('methodName', 'params');
        $this->handlers[$this->index] = compact('onSuccess', 'onError');
        ++$this->index;

        return $this;
    }

    /** {@inheritdoc} */
    public function onSuccess(callable $onSuccess)
    {
        $this->onSuccess = $onSuccess;

        return $this;
    }

    /** {@inheritdoc} */
    public function onError(callable $onError)
    {
        $this->onError = $onError;

        return $this;
    }

    /** {@inheritdoc} */
    public function execute()
    {
        $results = $this->client->call('system.multicall', [$this->calls]);

        foreach ($results as $index => $result) {
            $this->processResult($this->handlers[$index], $result);
        }

        return $results;
    }

    /**
     * @param array $handler
     * @param mixed $result
     */
    protected function processResult(array $handler, $result)
    {
        $isError = is_array($result) && isset($result['faultCode']);

        $this->invokeHandler($handler['onSuccess'], $handler['onError'], $isError, $result);
        $this->invokeHandler($this->onSuccess, $this->onError, $isError, $result);
    }

    /**
     * @param callable|null $onSuccess
     * @param callable|null $onError
     * @param boolean       $isError
     * @param mixed         $result
     */
    protected function invokeHandler($onSuccess, $onError, $isError, $result)
    {
        if ($isError && $onError !== null) {
            call_user_func($onError, $result);
        } elseif ($onSuccess !== null) {
            call_user_func($onSuccess, $result);
        }
    }

    /** @return ClientInterface */
    public function getClient()
    {
        return $this->client;
    }
}
