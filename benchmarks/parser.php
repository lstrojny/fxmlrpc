<?php
include __DIR__ . '/../vendor/autoload.php';

$start = 0;
$limit = 100;
$r = null;
$xml = file_get_contents(__DIR__ . '/response.xml');



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $s = new SimpleXmlElement($xml);
    $r = Zend\XmlRpc\Value::getXmlRpcValue($s->params->param->value->asXml(), Zend\XmlRpc\Value::XML_STRING);
}
$end = microtime(true);
printf("Zend\XmlRpc\\Value (ZF2): %s sec\n", $end - $start);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $s = new SimpleXmlElement($xml);
    $r = Zend_XmlRpc_Value::getXmlRpcValue($s->params->param->value->asXml(), Zend_XmlRpc_Value::XML_STRING);
}
$end = microtime(true);
printf("Zend_XmlRpc_Value (ZF1): %s sec\n", $end - $start);



$start = microtime(true);
$parser = new FXMLRPC\Parser\XMLReaderParser();
for ($a = 0; $a < $limit; ++$a) {
    $r = $parser->parse($xml, $isFault);
}
$end = microtime(true);
printf("FXMLRPC\Parser\XMLReaderParser: %s sec\n", $end - $start);



$start = microtime(true);
$parser = new FXMLRPC\Parser\NativeParser();
for ($a = 0; $a < $limit; ++$a) {
    $r = $parser->parse($xml, $isFault);
}
$end = microtime(true);
printf("FXMLRPC\Parser\\NativeParser: %s sec\n", $end - $start);
