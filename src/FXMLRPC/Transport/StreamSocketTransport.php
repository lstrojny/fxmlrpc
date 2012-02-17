<?php
namespace FXMLRPC\Transport;

use RuntimeException;

class StreamSocketTransport implements TransportInterface
{
    public function send($uri, $request)
    {
        $context = stream_context_create(
            array(
                'http' => array(
                    'method'  => 'POST',
                    'header'  => 'Content-Type: text/xml',
                    'content' => $request,
                )
            )
        );

        $response = @file_get_contents($uri, false, $context);
        if ($response === false) {
            throw new RuntimeException(error_get_last());
        }

        return $response;
    }
}