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
FXMLRPC\Parser\XMLReaderParser: 3.4971699714661 sec
FXMLRPC\Parser\NativeParser: 1.6693658828735 sec
Zend_XmlRpc: 24.519498109818 sec
```

### Serializer
```
FXMLRPC\Serializer\XMLWriterSerializer: 1.9878270626068 sec
FXMLRPC\Serializer\NativeSerializer: 0.86053609848022 sec
Zend_XmlRpc: 6.3274731636047 sec
```
