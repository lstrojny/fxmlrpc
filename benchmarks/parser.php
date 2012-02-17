<?php
include __DIR__ . '/../autoload.php';
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/../../Internations/source/in/vendor/zf/library/');

$start = 0;
$limit = 10;

$xml = file_get_contents('response.xml');

$start = microtime(true);
$parser = new FXMLRPC\Parser\XMLReaderParser();
for ($a = 0; $a < $limit; ++$a) {
    $r1 = $parser->parse($xml);
}
$end = microtime(true);
printf("FXMLRPC\Parser\XMLReaderParser: %s sec\n", $end - $start);

$start = microtime(true);
$parser = new FXMLRPC\Parser\NativeParser();
for ($a = 0; $a < $limit; ++$a) {
    $r2 = $parser->parse($xml);
}
$end = microtime(true);
printf("FXMLRPC\Parser\\NativeParser: %s sec\n", $end - $start);

$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $s = new SimpleXmlElement($xml);
    $r2 = Zend_XmlRpc_Value::getXmlRpcValue($s->params->param->value->asXml(), Zend_XmlRpc_Value::XML_STRING);
}
$end = microtime(true);
printf("Zend_XmlRpc: %s sec\n", $end - $start);
