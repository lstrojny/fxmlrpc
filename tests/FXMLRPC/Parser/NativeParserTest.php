<?php
namespace FXMLRPC\Parser;

use DateTime;
use DateTimeZone;

class NativeParserTest extends AbstractParserTest
{
    public function setUp()
    {
        if (!extension_loaded('xmlrpc')) {
            $this->markTestSkipped('ext/xmlrpc not available');
        }

        $this->parser = new NativeParser();
    }
}