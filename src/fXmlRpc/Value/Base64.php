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
namespace fXmlRpc\Value;

final class Base64 implements Base64Interface
{
    /** @var string */
    private $encoded;

    /** @var string */
    private $decoded;

    /**
     * @param string $encoded
     * @param string $decoded
     */
    private function __construct($encoded, $decoded)
    {
        $this->encoded = $encoded;
        $this->decoded = $decoded;
    }

    /**
     * Return new base64 value object by encoded value
     *
     * @param  string $string
     * @return Base64
     */
    public static function serialize($string)
    {
        return new static(null, $string);
    }

    /**
     * Return new base64 value by string
     *
     * @param  string $value
     * @return Base64
     */
    public static function deserialize($value)
    {
        return new static(trim($value), null);
    }

    /** {@inheritdoc} */
    public function getEncoded()
    {
        if ($this->encoded === null) {
            $this->encoded = base64_encode($this->decoded);
        }

        return $this->encoded;
    }

    /** {@inheritdoc} */
    public function getDecoded()
    {
        if ($this->decoded === null) {
            $this->decoded = base64_decode($this->encoded);
        }

        return $this->decoded;
    }
}
