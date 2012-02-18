# FXMLRPC: really fast XML/RPC for PHP

 - A convenient, object oriented API (similar to Zend’s XmlRpc client)
 - Serializing and parsing is very fast
 - Provides integration with various HTTP clients like Buzz, ZF HTTP client, Guzzle

[![Build Status](https://secure.travis-ci.org/lstrojny/fxmlrpc.png)](http://travis-ci.org/lstrojny/fxmlrpc)

## Usage

### Basic Usage
```php
<?php
$client = new FXMLRPC\Client('http://endpoint.com');
$client->call('remoteMethod', array('arg1', true));
```


### Using native (ext/xmlrpc based) serializer/parser (for even better performance)
```php
<?php
$client = new FXMLRPC\Client(
    'http://endpoint.com',
    null,
    new FXMLRPC\Parser\NativeParser(),
    new FXMLRPC\Serializer\NativeSerializer()
);
$client->call('remoteMethod', array('arg1', true));
```

### Integrate with various HTTP client
```php
<?php
$browser = new Buzz\Browser();
$browser->...();
$client = new FXMLRPC\Client(
    'http://endpoint.com',
    new FXMLRPC\Transport\BuzzBrowserBridge($browser)
);

$client = new Zend_Http_Client();
$client->...();
$client = new FXMLRPC\Client(
    'http://endpoint.com',
    new FXMLRPC\Transport\ZF1HttpClientBridge($client)
);

$httpClient = new Zend\Http\Client();
$httpClient->...();
$client = new FXMLRPC\Client(
    'http://endpoint.com',
    new FXMLRPC\Transport\ZF2HttpClientBridge($httpClient)
);

$httpClient = new Guzzle\Http\Client();
$httpClient->...();
$client = new FXMLRPC\Client(
    'http://endpoint.com',
    new FXMLRPC\Transport\GuzzleBridge($httpClient)
);

$request = new HttpRequest();
$request->...();
$client = new FXMLRPC\Client(
    'http://endpoint.com',
    new FXMLRPC\Transport\PeclHttpBridge($request)
);
```

## How fast?

### Parser
```
Zend\XmlRpc\Value (ZF2): 24.879510879517 sec
Zend_XmlRpc_Value (ZF1): 24.3286921978 sec
FXMLRPC\Parser\XMLReaderParser: 3.7781069278717 sec
FXMLRPC\Parser\NativeParser: 1.8550541400909 sec
```

### Serializer
```
Zend\XmlRpc\Request (ZF2): 5.1440720558167 sec
Zend_XmlRpc_Request (ZF1): 6.4965651035309 sec
FXMLRPC\Serializer\XMLWriterSerializer: 2.3370549678802 sec
FXMLRPC\Serializer\NativeSerializer: 0.97839784622192 sec
```
