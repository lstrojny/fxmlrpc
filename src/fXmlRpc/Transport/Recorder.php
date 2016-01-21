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
namespace fXmlRpc\Transport;
use Exception;

/**
 * Transport decorator which contains XML of the last request and response.
 *
 * @author Piotr Olaszewski <piotroo89 [%] gmail dot com>
 */
class Recorder implements TransportInterface
{
    /** @var TransportInterface */
    private $transport;

    /** @var string|null */
    private $lastRequest = null;

    /** @var string|null */
    private $lastResponse = null;

    /** @var Exception|null */
    private $lastException = null;

    public function __construct(TransportInterface $transport)
    {
        $this->transport = $transport;
    }

    /** {@inheritdoc} */
    public function send($endpoint, $payload)
    {
        $this->lastResponse = $this->lastException = null;
        $this->lastRequest = $payload;

        try {
            $this->lastResponse = $this->transport->send($endpoint, $payload);

            return $this->lastResponse;
        } catch (Exception $e) {
            $this->lastException = $e;

            throw $e;
        }
    }

    /**
     * Returns the XML sent in the last request.
     *
     * @return string|null
     */
    public function getLastRequest()
    {
        return $this->lastRequest;
    }

    /**
     * Returns the XML received in the last response.
     *
     * @return string|null
     */
    public function getLastResponse()
    {
        return $this->lastResponse;
    }

    /**
     * Returns exception when last request fail.
     *
     * @return Exception|null
     */
    public function getLastException()
    {
        return $this->lastException;
    }
}
