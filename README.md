# FXMLRPC: really fast XML/RPC for PHP
An object oriented XML/RPC client designed as a replacement for Zend_XmlRpc


[![Build Status](https://secure.travis-ci.org/lstrojny/fxmlrpc.png)](http://travis-ci.org/lstrojny/fxmlrpc)


### Preliminary benchmarks

#### Serializer
```
FXMLRPC\Parser\XMLReaderParser: 3.4971699714661 sec
FXMLRPC\Parser\NativeParser: 1.6693658828735 sec
Zend_XmlRpc: 24.519498109818 sec
```
