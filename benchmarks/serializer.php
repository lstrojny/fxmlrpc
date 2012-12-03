<?php
include __DIR__ . '/../vendor/autoload.php';
ini_set('date.timezone', 'UTC');

$start = 0;
$limit = 40;

$args = array();
for ($a = 0; $a < 1000; $a++) {
    $args[] = array(
        'test_string' => array($a => str_repeat('a', $a)),
        'test_integer' => (int) rand(),
        'test_float' => (float) rand(),
        'test_datetime' => new DateTime(),
        'test_base64' => fXmlRpc\Value\Base64::serialize(str_repeat('a', $a)),
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
printf("Zend\\XmlRpc\\Request (ZF2): %s sec for %d passes\n", $end - $start, $limit);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $request = new Zend_XmlRpc_Request();
    $request->setMethod('test');
    $request->setParams($args);
    $r = $request->saveXml();
}
$end = microtime(true);
printf("Zend_XmlRpc_Request (ZF1): %s sec for %d passes\n", $end - $start, $limit);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $serializer = new fXmlRpc\Serializer\XmlWriterSerializer();
    $r = $serializer->serialize('test', $args);
}
$end = microtime(true);
printf("fXmlRpc\Serializer\XmlWriterSerializer: %s sec for %d passes\n", $end - $start, $limit);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $serializer = new fXmlRpc\Serializer\NativeSerializer();
    $r = $serializer->serialize('test', $args);
}
$end = microtime(true);
printf("fXmlRpc\Serializer\\NativeSerializer: %s sec for %d passes\n", $end - $start, $limit);

file_put_contents('response.xml', $r);
