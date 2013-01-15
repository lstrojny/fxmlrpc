# fxmlrpc: really fast XML/RPC for PHP [![Build Status](https://secure.travis-ci.org/lstrojny/fxmlrpc.png)](http://travis-ci.org/lstrojny/fxmlrpc)

 - A convenient, object oriented API (similar to the XML/RPC client in Zend Framework)
 - Very fast serializing and parsing of the XML payloads involved
 - Stick to the HTTP client you already use: Buzz, ZF1/ZF2 HTTP client, Guzzle, PECL HTTP
 - Licensed under the terms of the liberal MIT license
 - Supports modern standards: easy installation via composer, fully PSR-0, PSR-1 and PSR-2 compatible
 - Relentlessly unit- and integration tested
 - Implements all known XML/RPC extensions

## Latest improvements

 - `[BUG]` Fixing huge issue in `XmlWriterSerializer` (see #4 for details)
 - `[FEATURE]` Special API for multicall
 - `[FEATURE]` Supports all Java XML/RPC extensions
 - `[BC]` `fXmlRpc\AbstractDecorator` and `fXmlRpc\ClientInterface` now includes methods to prepend and append parameters
 - `[BC]` `fXmlRpc\Client` is marked as final. Properties marked as private. Extend via decorator.
 - `[BC]` Marked deprecated constructor of `fXmlRpc\Value\Base64` as private. Additionally, the value object is final now
 - `[TESTING]` Integration test suite against Java XML/RPC and Python XML/RPC
 - `[BUG]` Fixing implicit string type handling (where string is no child of value)
 - `[IMPROVEMENT]` Improved exception handling
 - `[BC]` Changing naming scheme to studly caps
 - `[BUG]` Fixing various array/struct edge cases
 - `[IMPROVEMENT]` Small memory and performance improvements for serializers and parsers
 - `[BC]` Deprecated constructor of `fXmlRpc\Value\Base64` and introduced `::serialize()` an `::deserialize()` instead.
 - `[FEATURE]` Adding `fXmlRpc\Client::prependParams()` and `fXmlRpc\Client::appendParams()` to set default params. This helps e.g. when you need to add authorization information for every call
 - `[FEATURE]` Timing Loggers now support threshold based logging to ease controlling your servers responds in a certain time
 - `[TESTING]` Travis now runs the test suite against various versions of supported HTTP clients and logging components.

### How fast is it really?

IO performance is out of reach from a userspace perspective, but parsing and
serialization speed is what matters. How fast can we generate the XML payload
from PHP data structures and how fast can we parse the servers response? fXmlRpc
uses stream based XML writers/readers to achieve it’s performance and heavily
optimizes (read uglifies) for it. As as result the userland version is only
around 2x slower than the native C implementation (ext/xmlrpc).


#### Parser
```
Zend\XmlRpc\Value (ZF2): 249.02972793579 sec
Zend_XmlRpc_Value (ZF1): 253.88145494461 sec
fXmlRpc\Parser\XmlReaderParser: 36.274516105652 sec
fXmlRpc\Parser\NativeParser: 18.652323007584 sec
```

#### Serializer
```
Zend\XmlRpc\Request (ZF2): 52.004573106766 sec
Zend_XmlRpc_Request (ZF1): 65.042532920837 sec
fXmlRpc\Serializer\XmlWriterSerializer: 23.652673006058 sec
fXmlRpc\Serializer\NativeSerializer: 9.0790779590607 sec
```


### Usage

#### Basic Usage
```php
<?php
$client = new fXmlRpc\Client('http://endpoint.com');
$client->call('remoteMethod', array('arg1', true));
```

#### Using native (ext/xmlrpc based) serializer/parser (for even better performance)
```php
<?php
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    null,
    new fXmlRpc\Parser\NativeParser(),
    new fXmlRpc\Serializer\NativeSerializer()
);
$client->call('remoteMethod', array('arg1', true));
```

#### Prepending and appending arguments
```php
<?php
$client = new fXmlRpc\Client('http://endpoint.com');
$client->prependParams(array('username', 'password'));
$client->appendParams(array('appended'));
...
```

#### Using a convenient Proxy object
```php
<?php
$proxy = new fXmlRpc\Proxy(new fXmlRpc\Client('http://endpoint.com'));
// Call system.echo
$proxy->system->echo('Hello World!');
```

### Helpful abstraction for multicall requests
```php
<?php
$result = $client->multicall()
    ->addCall('system.add', array(1, 2))
    ->addCall(
        'system.add',
        array(2, 3),
        function ($result) {
            echo "Result was: " . $result;
        },
        function($result) {
            echo "An error occured: " . var_export($result, true);
        }
    )
    ->onSuccess(function ($result) {echo "Success";}) // Success handler for each call
    ->onError(function ($result) {echo "Error";}) // Error handler for each call
    ->execute();
```

#### Integration for various HTTP clients
```php
<?php
/** Buzz (https://github.com/kriswallsmith/Buzz) */
$browser = new Buzz\Browser();
$browser->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\BuzzBrowserBridge($browser)
);

/** Zend Framework 1 (http://framework.zend.com/) */
$httpClient = new Zend_Http_Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\ZF1HttpClientBridge($httpClient)
);

/** Zend Framework 2 (http://framework.zend.com/zf2) */
$httpClient = new Zend\Http\Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\ZF2HttpClientBridge($httpClient)
);

/** Guzzle (http://guzzlephp.org/) */
$httpClient = new Guzzle\Http\Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\GuzzleBridge($httpClient)
);

/** PECL HTTP (http://pecl.php.net/pecl_http) */
$request = new HttpRequest();
$request->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\PeclHttpBridge($request)
);
```

#### Timing XML/RPC requests to find problematic calls
fXmlRpc allows you to time your XML/RPC request, to find out which took how long. It provides a
`fXmlRpc\Timing\TimingDecorator` which can be used with various timers implementing
`fXmlRpc\Timing\TimerInterface`. Currently implemented are bridges for Monolog, Zend Framework 1
`Zend_Log` and Zend Framework 2 `Zend\Log`.

Usage:
```php
<?php
$client = new fXmlRpc\Timing\TimingDecorator(
    new fXmlRpc\Client(...),
    new fXmlRpc\Timing\MonologTimerBridge(
        $monolog,
        Monolog\Logger::ALERT,
        'My custom log message template %F'
    )
);
```
