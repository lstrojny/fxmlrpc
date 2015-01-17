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

use Zend_Http_Client as HttpClient;
use Zend_Http_Client_Adapter_Exception as HttpClientAdapterException;
use Zend_Http_Client_Exception as HttpClientException;
use fXmlRpc\Exception\HttpException;
use fXmlRpc\Exception\TcpException;

class ZendFrameworkOneHttpClientBridge extends AbstractHttpTransport
{
    /**
     * @var HttpClient
     */
    private $client;

    /**
     * @param HttpClient $client
     */
    public function __construct(HttpClient $client)
    {
        $this->client = $client;
    }

    /**
     * {@inheritdoc}
     */
    public function send($url, $payload)
    {
        try {
            $response = $this->client
                ->setUri($url)
                ->setHeaders($this->getHeaders())
                ->setRawData($payload)
                ->request('POST');
        } catch (HttpClientAdapterException $e) {
            throw TcpException::transportError($e);
        } catch (HttpClientException $e) {
            throw TcpException::transportError($e);
        }

        if ($response->getStatus() !== 200) {
            throw HttpException::httpError($response->getMessage(), $response->getStatus());
        }

        return $response->getBody();
    }
}
