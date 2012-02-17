<?php
namespace FXMLRPC\Serializer;

interface SerializerInterface
{
    public function serialize($method, array $params = array());
}