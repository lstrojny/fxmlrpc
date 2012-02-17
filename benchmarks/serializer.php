<?php
include __DIR__ . '/../autoload.php';
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../Internations/source/in/vendor/zf/library/');

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

$start = microtime(true);
$serializer = new FXMLRPC\Serializer\XMLWriterSerializer();
for ($a = 0; $a < $limit; ++$a) {
    $r = $serializer->serialize('test', $args);
}
$end = microtime(true);
printf("FXMLRPC\Serializer\XMLWriterSerializer: %s sec\n", $end - $start);

$start = microtime(true);
$serializer = new FXMLRPC\Serializer\NativeSerializer();
for ($a = 0; $a < $limit; ++$a) {
    $r = $serializer->serialize('test', $args);
}
$end = microtime(true);
printf("FXMLRPC\Serializer\\NativeSerializer: %s sec\n", $end - $start);

$start = microtime(true);
$request = new Zend_XmlRpc_Request();
for ($a = 0; $a < $limit; ++$a) {
    $request->setMethod('test');
    $request->setParams($args);
    $r = $request->saveXml();
}
$end = microtime(true);
printf("Zend_XmlRpc: %s sec\n", $end - $start);
