<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/src');
spl_autoload_register(function($className) {
    require_once str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . '.php';
});
