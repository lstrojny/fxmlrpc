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

class Base64Test extends \PHPUnit_Framework_TestCase
{
    public function testWithDecodedString()
    {
        $base64 = Base64::serialize('string');
        $this->assertSame('string', $base64->getDecoded());
        $this->assertSame('c3RyaW5n', $base64->getEncoded());
    }

    public function testWithEncodedString()
    {
        $base64 = Base64::deserialize('c3RyaW5n');
        $this->assertSame('string', $base64->getDecoded());
        $this->assertSame('c3RyaW5n', $base64->getEncoded());
    }

    public function testEncodedStringIsTrimmed()
    {
        $base64 = Base64::deserialize("\nc3RyaW5n   ");
        $this->assertSame('string', $base64->getDecoded());
        $this->assertSame('c3RyaW5n', $base64->getEncoded());
    }
}
