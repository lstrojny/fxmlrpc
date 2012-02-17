<?php
namespace FXMLRPC\Serializer;

class NativeSerializerTest extends AbstractSerializerTest
{
    public function setUp()
    {
        $this->serializer = new NativeSerializer();
    }
}