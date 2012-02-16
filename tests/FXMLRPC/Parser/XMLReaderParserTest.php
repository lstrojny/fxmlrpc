<?php
namespace FXMLRPC\Parser;

use DateTime;
use DateTimeZone;

class XMLReaderParserTest extends AbstractParserTest
{
    public function setUp()
    {
        if (!extension_loaded('xmlreader')) {
            $this->markTestSkipped('ext/xmlreader not available');
        }

        $this->parser = new XMLReaderParser();
    }
}