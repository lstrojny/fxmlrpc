<?php
set_include_path(get_include_path() . PATH_SEPARATOR . __DIR__ . '/src');
spl_autoload_register(function($className) {
    $file = str_replace(array('\\', '_'), DIRECTORY_SEPARATOR, $className) . '.php';
    if ($file = stream_resolve_include_path($file)) {
        include $file;
    }
});
