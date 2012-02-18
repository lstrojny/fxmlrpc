<?php
namespace FXMLRPC\Transport;

use HttpRequest;
use RuntimeException;

class PeclHttpBridge implements TransportInterface
{
    private $request;

    public function __construct(HttpRequest $request)
    {
        $this->request = $request;
    }

    public function send($uri, $requestXml)
    {
        $this->request->setUrl($uri);
        $this->request->setMethod(HttpRequest::METH_POST);
        $this->request->setRawPostData($requestXml);
        $response = $this->request->send();

        if ($response->getResponseCode() !== 200) {
            throw new RuntimeException('HTTP error: ' . $response->getResponseStatus());
        }

        return $response->getBody();
    }
}