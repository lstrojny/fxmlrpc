<?php
namespace FXMLRPC\Transport;

use Buzz\Browser;
use RuntimeException;

class BuzzBrowserBridge implements TransportInterface
{
    /**
     * @var Buzz\Browser
     */
    private $browser;

    public function __construct(Browser $browser)
    {
        $this->browser = $browser;
    }

    public function send($uri, $payload)
    {
        $response = $this->browser->post($uri, array(), $payload);

        if ($response->getStatusCode() !== 200) {
            throw new RuntimeException('HTTP error: ' . $response->getReasonPhrase());
        }

        return $response->getContent();
    }
}