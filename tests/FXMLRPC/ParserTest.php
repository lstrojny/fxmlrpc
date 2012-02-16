<?php
namespace FXMLRPC;

class ParserTest extends \PHPUnit_Framework_TestCase
{
    public function __construct()
    {
        $this->parser = new Parser();
    }

    public function testParsingSimpleMethodResponse()
    {
        $string = '<?xml version="1.0"?>
            <methodResponse>
              <params>
                <param>
                  <value><string>Ümlaut String</string></value>
                </param>
              </params>
            </methodResponse>';

        $this->assertSame(array('Ümlaut String'), $this->parser->parse($string));
    }

    public function testParsingMultiMethodResponse()
    {
        $string = '<?xml version="1.0"?>
            <methodResponse>
              <params>
                <param>
                  <value><string>Ümlaut String</string></value>
                </param>
                <param>
                  <value><string>Normal String</string></value>
                </param>
              </params>
            </methodResponse>';

        $this->assertSame(array('Ümlaut String', 'Normal String'), $this->parser->parse($string));
    }

    public function testParsingListResponse()
    {
        $string = '<?xml version="1.0"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <array>
                                <data>
                                    <value><string>Str 0</string></value>
                                    <value><string>Str 1</string></value>
                                </data>
                            </array>
                        </value>
                    </param>
                </params>
            </methodResponse>';
        $this->assertSame(array(array('Str 0', 'Str 1')), $this->parser->parse($string));
    }

    public function testParsingNestedListResponse()
    {
        $string = '<?xml version="1.0"?>
            <methodResponse>
                <params>
                    <param>
                        <value>
                            <array>
                                <data>
                                    <value>
                                        <array>
                                            <data>
                                                <value><string>Str 00</string></value>
                                                <value><string>Str 01</string></value>
                                            </data>
                                        </array>
                                    </value>
                                    <value>
                                        <array>
                                            <data>
                                                <value><string>Str 10</string></value>
                                                <value><string>Str 11</string></value>
                                            </data>
                                        </array>
                                    </value>
                                </data>
                            </array>
                        </value>
                    </param>
                </params>
            </methodResponse>';
        $this->assertSame(
            array(array(array('Str 00', 'Str 01'), array('Str 10', 'Str 11'))),
            $this->parser->parse($string)
        );
    }
}
