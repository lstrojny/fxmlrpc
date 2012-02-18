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

    public function send($uri, $requestXml)
    {
        try {
            $response = $this->client->post($uri, null, $requestXml)
                                     ->send();
        } catch (BadResponseException $e) {
            throw new RuntimeException('HTTP error: ' . $e->getMessage());
        }

        return $response->getBody(true);
    }
}