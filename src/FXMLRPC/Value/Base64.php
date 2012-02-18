<?php
namespace FXMLRPC\Value;

class Base64 implements Base64Interface
{
    private $encoded;

    private $decoded;

    public function __construct($string, $isEncoded = false)
    {
        if ($isEncoded) {
            $this->encoded = $string;
        } else {
            $this->decoded = $string;
        }
    }

    public function getEncoded()
    {
        if ($this->encoded === null) {
            $this->encoded = base64_encode($this->decoded);
        }

        return $this->encoded;
    }

    public function getDecoded()
    {
        if ($this->decoded === null) {
            $this->decoded = base64_decode($this->encoded);
        }

        return $this->decoded;
    }
}