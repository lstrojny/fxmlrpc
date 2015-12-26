<?php
require_once __DIR__ . '/../vendor/autoload.php';

$start = 0;
$limit = 40;
$r = null;
$xml = file_get_contents(__DIR__ . '/response.xml');



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $s = new SimpleXmlElement($xml);
    $r = Zend\XmlRpc\AbstractValue::getXmlRpcValue($s->params->param->value->asXml(), Zend\XmlRpc\AbstractValue::XML_STRING);
}
$end = microtime(true);
printf("Zend\XmlRpc\\AbstractValue (ZF2): %s sec for %d passes\n", $end - $start, $limit);



$start = microtime(true);
for ($a = 0; $a < $limit; ++$a) {
    $s = new SimpleXmlElement($xml);
    $r = Zend_XmlRpc_Value::getXmlRpcValue($s->params->param->value->asXml(), Zend_XmlRpc_Value::XML_STRING);
}
$end = microtime(true);
printf("Zend_XmlRpc_Value (ZF1): %s sec for %d passes\n", $end - $start, $limit);



$start = microtime(true);
$parser = new fXmlRpc\Parser\XmlReaderParser();
for ($a = 0; $a < $limit; ++$a) {
    $r = $parser->parse($xml);
}
$end = microtime(true);
printf("fXmlRpc\Parser\XmlReaderParser: %s sec for %d passes\n", $end - $start, $limit);



$start = microtime(true);
$parser = new fXmlRpc\Parser\NativeParser();
for ($a = 0; $a < $limit; ++$a) {
    $r = $parser->parse($xml);
}
$end = microtime(true);
printf("fXmlRpc\Parser\\NativeParser: %s sec for %d passes\n", $end - $start, $limit);
