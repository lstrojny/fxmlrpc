<?php
namespace FXMLRPC\Transport;

interface TransportInterface
{
    public function send($uri, $payload);
}