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

abstract class AbstractDecorator implements ClientInterface
{
    protected $wrapped;

    public function __construct(ClientInterface $wrapped)
    {
        $this->wrapped = $wrapped;
    }

    public function setUri($uri)
    {
        return $this->wrapped->setUri($uri);
    }

    public function getUri()
    {
        return $this->wrapped->getUri();
    }

    public function prependParams(array $params)
    {
        return $this->wrapped->prependParams($params);
    }

    public function getPrependParams()
    {
        return $this->wrapped->getPrependParams();
    }

    public function appendParams(array $params)
    {
        return $this->wrapped->appendParams($params);
    }

    public function getAppendParams()
    {
        return $this->wrapped->getAppendParams();
    }

    public function call($methodName, array $arguments = array())
    {
        return $this->wrapped->call($methodName, $arguments);
    }
}
