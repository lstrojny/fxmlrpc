<?php
namespace FXMLRPC\Parser;

use DateTime;
use DateTimeZone;
use stdClass;

class NativeParser implements ParserInterface
{
    public function parse($xmlString)
    {
        $result = xmlrpc_decode($xmlString, 'UTF-8');

        $heap = array(&$result);
        while (isset($heap[0]) && $value = &$heap[0]) {

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
                        $heap[] = &$element;
                    }
                    break;
            }

            array_shift($heap);
        }

        return $result;
    }
}