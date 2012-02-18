<?php
namespace FXMLRPC\Exception;

use RuntimeException;

class ResponseException extends RuntimeException
{
    private $faultCode;

    public function __construct($faultString, $faultCode)
    {
        parent::__construct($faultString);
        $this->faultCode = $faultCode;
    }

    public function getFaultString()
    {
        return $this->getMessage();
    }

    public function getFaultCode()
    {
        return $this->faultCode;
    }
}