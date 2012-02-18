<?php
namespace FXMLRPC;

use FXMLRPC\Transport\TransportInterface;
use FXMLRPC\Transport\StreamSocketTransport;
use FXMLRPC\Parser\ParserInterface;
use FXMLRPC\Parser\XMLReaderParser;
use FXMLRPC\Serializer\SerializerInterface;
use FXMLRPC\Serializer\XMLWriterSerializer;
use FXMLRPC\Exception\ResponseException;

class Client
{
    protected $uri;

    protected $transport;

    protected $parser;

    protected $serializer;

    public function __construct(
        $uri = null,
        TransportInterface $transport = null,
        ParserInterface $parser = null,
        SerializerInterface $serializer = null
    )
    {
        $this->uri = $uri;
        $this->transport = $transport ?: new StreamSocketTransport();
        $this->parser = $parser ?: new XMLReaderParser();
        $this->serializer = $serializer ?: new XMLWriterSerializer();
    }

    public function setUri($uri)
    {
        $this->uri = $uri;
    }

    public function getUri()
    {
        return $this->uri;
    }

    public function call($method, array $params = array())
    {
        $request = $this->serializer->serialize($method, $params);
        $response = $this->transport->send($this->uri, $request);

        $data = $this->parser->parse($response);
        if (is_array($data) && isset($data['faultCode'])) {
            throw new ResponseException($data['faultString'], $data['faultCode']);
        }

        return $data;
    }
}