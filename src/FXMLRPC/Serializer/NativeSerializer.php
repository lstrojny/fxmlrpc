<?php
namespace FXMLRPC\Serializer;

use DateTime;
use FXMLRPC\Value\Base64Interface;

class NativeSerializer implements SerializerInterface
{
    public function __construct()
    {
        if (!extension_loaded('xmlrpc')) {
            throw new RuntimeException('PHP extension ext/xmlrpc missing');
        }
    }

    public function serialize($method, array $params = array())
    {
        $toBeVisited = array(&$params);
        while (isset($toBeVisited[0]) && $value = &$toBeVisited[0]) {

            switch (gettype($value)) {
                case 'array':
                    foreach ($value as &$v) {
                        $toBeVisited[] = &$v;
                    }
                    break;

                case 'object':
                    if ($value instanceof DateTime) {
                        $value = $value->format('Ymd\TH:i:s');
                        xmlrpc_set_type($value, 'datetime');
                        break;
                    }

                    if ($value instanceof Base64Interface) {
                        $value = $value->getDecoded();
                        xmlrpc_set_type($value, 'base64');
                        break;
                    }

                    $value = get_object_vars($value);
                    break;
            }

            array_shift($toBeVisited);
        }

        return xmlrpc_encode_request($method, $params, array('encoding' => 'UTF-8'));
    }
}