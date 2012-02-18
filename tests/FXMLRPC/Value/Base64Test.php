<?php
namespace FXMLRPC\Value;

class Base64Test extends \PHPUnit_Framework_TestCase
{
    public function testWithDecodedString()
    {
        $base64 = new Base64('string');
        $this->assertSame('string', $base64->getDecoded());
        $this->assertSame('c3RyaW5n', $base64->getEncoded());
    }

    public function testWithEncodedString()
    {
        $base64 = new Base64('c3RyaW5n', true);
        $this->assertSame('string', $base64->getDecoded());
        $this->assertSame('c3RyaW5n', $base64->getEncoded());
    }
}