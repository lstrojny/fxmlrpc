# fxmlrpc: really fast XML/RPC for PHP

[![Gitter](https://badges.gitter.im/Join%20Chat.svg)](https://gitter.im/lstrojny/fxmlrpc?utm_source=badge&utm_medium=badge&utm_campaign=pr-badge&utm_content=badge)
[![Build Status](https://secure.travis-ci.org/lstrojny/fxmlrpc.svg)](http://travis-ci.org/lstrojny/fxmlrpc) [![Dependency Status](https://www.versioneye.com/user/projects/523ed7eb632bac1b0600bae8/badge.png)](https://www.versioneye.com/user/projects/523ed7eb632bac1b0600bae8) [![Average time to resolve an issue](http://isitmaintained.com/badge/resolution/lstrojny/fxmlrpc.svg)](http://isitmaintained.com/project/lstrojny/fxmlrpc "Average time to resolve an issue") [![Percentage of issues still open](http://isitmaintained.com/badge/open/lstrojny/fxmlrpc.svg)](http://isitmaintained.com/project/lstrojny/fxmlrpc "Percentage of issues still open")

 - A convenient, object oriented API (similar to the XML/RPC client in Zend Framework)
 - Very fast serializing and parsing of the XML payloads involved
 - Stick to the HTTP client you already use provided by [Ivory Http Adapter](https://github.com/egeloen/ivory-http-adapter)
 - Licensed under the terms of the liberal MIT license
 - Supports modern standards: easy installation via composer, fully PSR-0, PSR-1 and PSR-2 compatible
 - Relentlessly unit- and integration tested
 - Implements all known XML/RPC extensions

## Upgrading to 0.20.x
We change ParserInterface::parse() method interface, now isn't required to pass second parameter ($isFault), parser should throw an exception FaultException when fault message is encountered in server response.

## Upgrading to 0.10.x
0.10.x comes with a couple of breaking changes, see the migration guide below.

### Ivory HTTP adapter
We used to ship our own bridges for interoperability with various HTTP clients but moved that responsibility to a 3rd party library called [Ivory HTTP Adapter](https://github.com/egeloen/ivory-http-adapter).
*IMPORTANT NOTE:* the library is not installed by default as you could choose to use fxmlrpc with just your own implementation of the `fXmlRpc\Transport\TransportInterface`. To install the library – and that’s what you most likely want – add this line to your `composer.json`

```
"egeloen/http-adapter": "~0.6"
```

… and run `composer update`

### Instantiating an HTTP transport
In order to use the new adapters, you need to change how you instantiate fXmlRpc and its transport. This is how instantiating a custom transport looked before:

```php
$httpClient = new GuzzleHttp\Client();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\Guzzle4Bridge($httpClient)
);
```

This is how you do it now:
```php
$httpClient = new GuzzleHttp\Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\HttpAdapterTransport(new Ivory\HttpAdapter\GuzzleHttpHttpAdapter($httpClient))
);
```

## Latest improvements

 - `[IMPROVEMENT]` Refactor parsers throw fault exception instead of client (see #53, contribution by [Piotr Olaszewski](https://github.com/piotrooo))
 - `[FEATURE]` Add XML validation on the client side. Configurable but enabled per default
 - `[FEATURE]` Transport decorator which contains XML of the last request, response and exception (see #47, contribution by [Piotr Olaszewski](https://github.com/piotrooo))
 - `[BC]` PSR-4 for autoloading (see #29)
 - `[BC]` Rename `fXmlRpc\Multicall` to `fXmlRpc\MulticallBuilder`
 - `[BC]` Make the surface of the `ClientInterface` signifcantly smaller (see #24 for details)
 - `[BC]` Replaces built-in transports with [Ivory HTTP Adapter](https://github.com/egeloen/ivory-http-adapter). PECL HTTP is no longer supported. Contribution by [Márk Sági-Kazár](https://github.com/sagikazarmark)
 - `[BUG]` Fix serialization issue with XmlWriterSerializer (see #19 for details)
 - `[FEATURE]` New bridge for [artax](https://github.com/amphp/artax) (with contributions by [Markus Staab](https://github.com/staabm))
 - `[FEATURE]` New bridge for Guzzle 4 (contribution by [Robin van der Vleuten](https://github.com/RobinvdVleuten))
 - `[FEATURE]` Allow HTTP transport headers to be controlled
 - `[FEATURE]` Allow transport content type and charset to be controlled (see #9)
 - `[BC]` Removing outdated PeclHttpBridge
 - `[BC]` Requiring PHP 5.4
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

#### Tracking XML of the request and response
```php
<?php
$transport = new fXmlRpc\Transport\HttpAdapterTransport(...);
$recorder = new Recorder($transport);
$client = new Client('http://foo.com', $recorder);
$client->call('TestMethod', ['param1', 2, ['param3' => true]]);

$lastRequest = $recorder->getLastRequest();
$lastResponse = $recorder->getLastResponse();
```

If exception occur in the transport layer you can get it using `getLastException()`.

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

#### Integration for various HTTP clients using [Ivory](https://github.com/egeloen/ivory-http-adapter)
```php
<?php
/** Buzz (https://github.com/kriswallsmith/Buzz) */
$browser = new Buzz\Browser();
$browser->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\HttpAdapterTransport(new \Ivory\HttpAdapter\BuzzHttpAdapter($browser))
);

/** Zend Framework 1 (http://framework.zend.com/) */
$httpClient = new Zend_Http_Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\HttpAdapterTransport(new \Ivory\HttpAdapter\Zend1HttpAdapter($httpClient))
);

/** Zend Framework 2 (http://framework.zend.com/zf2) */
$httpClient = new Zend\Http\Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\HttpAdapterTransport(new \Ivory\HttpAdapter\Zend2HttpAdapter($httpClient))
);

/** Guzzle (http://guzzlephp.org/) */
$httpClient = new Guzzle\Http\Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\HttpAdapterTransport(new \Ivory\HttpAdapter\GuzzleAdapter($httpClient))
);

/** Guzzle 4+ (http://guzzlephp.org/) */
$httpClient = new GuzzleHttp\Client();
$httpClient->...();
$client = new fXmlRpc\Client(
    'http://endpoint.com',
    new fXmlRpc\Transport\HttpAdapterTransport(new \Ivory\HttpAdapter\GuzzleHttpHttpAdapter($httpClient))
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
