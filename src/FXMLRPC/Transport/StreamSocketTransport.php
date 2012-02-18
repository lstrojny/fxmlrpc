<?php
namespace FXMLRPC\Transport;

use RuntimeException;

class StreamSocketTransport implements TransportInterface
{
    public function send($uri, $payload)
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => 'Content-Type: text/xml',
                    'content' => $payload,
                )
            )
        );

        $response = @file_get_contents($uri, false, $context);
        if ($response === false) {
            $error = error_get_last();
            throw new RuntimeException('HTTP error: ' . $error['message']);
        }

        return $response;
    }
}