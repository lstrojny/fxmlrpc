# FXMLRPC: really fast XML/RPC for PHP
An object oriented XML/RPC client designed as a replacement for Zend_XmlRpc


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

### Integrate with Buzz HTTP client
```php
<?php
$browser = new Buzz\Browser();
$browser->...();
$client = new FXMLRPC\Client(
    'http://endpoint.com',
    new FXMLRPC\Transport\BuzzBrowserBridge($browser)
);
$client->call('remoteMethod', array('arg1', true));
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
