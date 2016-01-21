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

interface MulticallBuilderInterface
{
    /**
     * Register a success handler applicable to all multicall responses
     *
     * @param callable $handler
     * @throws InvalidArgumentException
     * @return MulticallBuilderInterface
     */
    public function onSuccess(callable $handler);

    /**
     * Register a error handler applicable to all multicall responses
     *
     * @param callable $handler
     * @throws InvalidArgumentException
     * @return MulticallBuilderInterface
     */
    public function onError(callable $handler);

    /**
     * Add a call to the end of the multicall stack
     *
     * @param string $methodName
     * @param array $params
     * @param callable $onSuccess
     * @param callable $onError
     * @return MulticallBuilderInterface
     */
    public function addCall($methodName, array $params = [], callable $onSuccess = null, callable $onError = null);

    /**
     * Send the multicall request to the server
     *
     * @return array
     */
    public function execute();
}
