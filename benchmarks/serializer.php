<?php
include __DIR__ . '/../vendor/autoload.php';

$start = 0;
$limit = 10;

$args = array();
for ($a = 0; $a < 1000; $a++) {
    $args[] = array(
        'test' => array($a => str_repeat('a', $a))
    );
    $args[] = $a;
    $args[] = str_repeat('ä', $a);
}

$r = null;
$request = null;
$serializer = null;

$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $request = new Zend\XmlRpc\Request();
    $request->setMethod('test');
    $request->setParams($args);
    $r = $request->saveXml();
}
$end = microtime(true);
printf("Zend\\XmlRpc\\Request (ZF2): %s sec\n", $end - $start);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $request = new Zend_XmlRpc_Request();
    $request->setMethod('test');
    $request->setParams($args);
    $r = $request->saveXml();
}
$end = microtime(true);
printf("Zend_XmlRpc_Request (ZF1): %s sec\n", $end - $start);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $serializer = new FXMLRPC\Serializer\XMLWriterSerializer();
    $r = $serializer->serialize('test', $args);
}
$end = microtime(true);
printf("FXMLRPC\Serializer\XMLWriterSerializer: %s sec\n", $end - $start);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $serializer = new FXMLRPC\Serializer\NativeSerializer();
    $r = $serializer->serialize('test', $args);
}
$end = microtime(true);
printf("FXMLRPC\Serializer\\NativeSerializer: %s sec\n", $end - $start);
