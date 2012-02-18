<?php
namespace FXMLRPC\Transport;

use Zend\Http\Client;
use RuntimeException;

class ZF2HttpClientBridge implements TransportInterface
{
    private $client;

    public function __construct(Client $client)
    {
        $this->client = $client;
    }

    public function send($url, $request)
    {
        $response = $this->client->setMethod('POST')
                                 ->setUri($url)
                                 ->setRawBody($request)
                                 ->send();

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('HTTP error: ' . $response->getReasonPhrase());
        }

        return $response->getBody();
    }
}
