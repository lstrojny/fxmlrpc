<?php
namespace FXMLRPC\Serializer;

use DateTime;

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
            if (is_array($value)) {
                foreach ($value as &$v) {
                    $toBeVisited[] = &$v;
                }
            } elseif ($value instanceof DateTime) {
                $value = $value->format('Ymd\TH:i:s');
                xmlrpc_set_type($value, 'datetime');
            } elseif (is_object($value)) {
                $value = get_object_vars($value);
            }

            array_shift($toBeVisited);
        }

        return xmlrpc_encode_request($method, $params, array('encoding' => 'UTF-8'));
    }
}