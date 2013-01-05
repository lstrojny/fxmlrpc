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

interface ClientInterface
{
    /**
     * Set endpoint URI
     *
     * @param string $uri
     * @return null
     */
    public function setUri($uri);

    /**
     * Return endpoint URI
     *
     * @return string
     */
    public function getUri();

    /**
     * Set default params to be prepended for each call (e.g. authorization information)
     *
     * @param array $params
     * @return null
     */
    public function prependParams(array $params);

    /**
     * @return array
     */
    public function getPrependParams();

    /**
     * Set default params to be appended for each call (e.g. authorization information)
     *
     * @param array $params
     * @return null
     */
    public function appendParams(array $params);

    /**
     * @return array
     */
    public function getAppendParams();

    /**
     * Execute remote call
     *
     * @param string $methodName
     * @param array  $params
     * @return mixed
     */
    public function call($methodName, array $params = array());

    /**
     * Start sequence of multiccallss
     *
     * @return Multicall
     */
    public function multicall();
}
