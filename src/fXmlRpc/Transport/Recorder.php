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
namespace fXmlRpc\Transport;

/**
 * Transport decorator which contains XML of the last request and response.
 *
 * @author Piotr Olaszewski <piotroo89 [%] gmail dot com>
 */
class Recorder implements TransportInterface
{
    /** @var TransportInterface */
    private $transport;
    private $lastRequest;
    private $lastResponse;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /** {@inheritdoc} */
    public function send($endpoint, $payload)
    {
        $this->lastRequest = $payload;
        $this->lastResponse = $this->transport->send($endpoint, $payload);
        return $this->lastResponse;
    }

    /**
     * Returns the XML sent in the last request.
     *
     * @return string
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Returns the XML received in the last response.
     *
     * @return string
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }
}
