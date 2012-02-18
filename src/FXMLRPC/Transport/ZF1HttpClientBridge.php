<?php
namespace FXMLRPC\Transport;

use Zend_Http_Client;
use RuntimeException;

class ZF1HttpClientBridge implements TransportInterface
{
    private $client;

    public function __construct(Zend_Http_Client $client)
    {
        $this->client = $client;
    }

    public function send($url, $payload)
    {
        $response =  $this->client->setUri($url)
                                  ->setRawData($payload)
                                  ->request('POST');

        if ($response->getStatus() !== 200) {
            throw new RuntimeException('HTTP error: ' . $response->getMessage());
        }

        return $response->getBody();
    }
}