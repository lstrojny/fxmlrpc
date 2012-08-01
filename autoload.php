<?php
set_include_path(
    __DIR__ . '/src'
    . PATH_SEPARATOR . __DIR__ . '/vendor/zf1/'
    . PATH_SEPARATOR . __DIR__ . '/vendor/zf2/library'
    . PATH_SEPARATOR . __DIR__ . '/vendor/buzz/lib'
    . PATH_SEPARATOR . __DIR__ . '/vendor/guzzle/src'
    . PATH_SEPARATOR . get_include_path()
);
spl_autoload_register(function($className) {
    $file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . '.php';
    if (strpos($className, 'Zend\\') === 0) {
        include __DIR__ . '/vendor/zf2/library/' . $file;
    } elseif (strpos($className, 'Zend_') === 0) {
        include __DIR__ . '/vendor/zf1/' . $file;
    } elseif (strpos($className, 'FXMLRPC\\') === 0) {
        include __DIR__ . '/src/' . $file;
    } elseif (strpos($className, 'Buzz\\') === 0) {
        include __DIR__ . '/vendor/buzz/lib/' . $file;
    } elseif (strpos($className, 'Guzzle\\') === 0) {
        include __DIR__ . '/vendor/guzzle/src/' . $file;
    } elseif (strpos($className, 'Symfony\\') === 0) {
        include __DIR__ . '/vendor/' . $file;
    } elseif (strpos($className, 'Monolog\\') === 0) {
        include __DIR__ . '/vendor/monolog/src/' . $file;
    }
});
