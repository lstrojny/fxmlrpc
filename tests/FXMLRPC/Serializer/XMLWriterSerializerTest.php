<?php
namespace FXMLRPC\Serializer;

class XMLWriterSerializerTest extends AbstractSerializerTest
{
    public function setUp()
    {
        $this->serializer = new XMLWriterSerializer();
    }
}