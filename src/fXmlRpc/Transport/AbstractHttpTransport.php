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

abstract class AbstractHttpTransport implements HttpTransportInterface
{
    /** @var array */
    private $headers = [];

    /** @var string */
    private $contentType = 'text/xml';

    /** @var string */
    private $charset = 'UTF-8';

    /** {@inheritdoc} */
    public function setHeader($header, $value)
    {
        $this->headers[$header] = $value;

        return $this;
    }

    /** {@inheritdoc} */
    public function setHeaders(array $headers)
    {
        $this->headers = array_replace($this->headers, $headers);

        return $this;
    }

    /** {@inheritdoc} */
    public function setContentType($contentType)
    {
        $this->contentType = $contentType === null ? 'text/xml' : $contentType;

        return $this;
    }

    /** {@inheritdoc} */
    public function setCharset($charset)
    {
        $this->charset = $charset;

        return $this;
    }

    /**
     * Return content type header string
     *
     * @return string
     */
    private function getContentTypeHeader()
    {
        if ($this->charset === null) {
            return $this->contentType;
        }

        return sprintf('%s; charset=%s', $this->contentType, $this->charset);
    }

    /**
     * Get header array
     *
     * @param bool $filterNullValues Filter null values from headers
     * @return array
     */
    protected function getHeaders($filterNullValues = false)
    {
        $headers = $this->headers;

        if ($filterNullValues) {
            $headers = array_filter($headers, static function ($v) {return $v !== null;});
        }

        $headers['Content-Type'] = $this->getContentTypeHeader();

        return $headers;
    }

    /**
     * Get headers as string
     *
     * @return string
     */
    protected function getHeadersString()
    {
        $headerString = '';
        foreach ($this->getHeaders(true) as $header => $value) {

            $headerString .= $header . ': ' . $value . "\r\n";
        }

        return $headerString;
    }
}
