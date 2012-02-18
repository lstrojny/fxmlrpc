<?php
namespace FXMLRPC\Transport;

use Guzzle\Http\Client;
use Guzzle\Http\Message\BadResponseException;
use RuntimeException;

class GuzzleBridge implements TransportInterface
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send($uri, $payload)
    {
        try {
            $response = $this->client->post($uri, null, $payload)
                                     ->send();
        } catch (BadResponseException $e) {
            throw new RuntimeException('HTTP error: ' . $e->getMessage());
        }

        return $response->getBody(true);
    }
}