<?php
namespace FXMLRPC\Parser;

use DateTime;
use DateTimeZone;
use stdClass;
use RuntimeException;

class NativeParser implements ParserInterface
{
    public function __construct()
    {
        if (!extension_loaded('xmlrpc')) {
            throw new RuntimeException('PHP extension ext/xmlrpc missing');
        }
    }

    public function parse($xmlString)
    {
        $result = xmlrpc_decode($xmlString, 'UTF-8');

        $toBeVisited = array(&$result);
        while (isset($toBeVisited[0]) && $value = &$toBeVisited[0]) {

            switch (gettype($value)) {
                case 'object':
                    switch ($value->xmlrpc_type) {

                        case 'datetime':
                            $value = DateTime::createFromFormat(
                                'Ymd\TH:i:s',
                                $value->scalar,
                                new DateTimeZone('UTC')
                            );
                            break;

                        case 'base64':
                            $value = $value->scalar;
                            break;
                    }
                    break;

                case 'array':
                    foreach ($value as &$element) {
                        $toBeVisited[] = &$element;
                    }
                    break;
            }

            array_shift($toBeVisited);
        }

        return $result;
    }
}
