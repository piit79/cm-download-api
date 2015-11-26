<?php

define('ROOT_PATH', __DIR__ . DIRECTORY_SEPARATOR . 'lib');

// PSR-0 autoloader
// http://zaemis.blogspot.fr/2012/05/writing-minimal-psr-0-autoloader.html
// slightly modified

spl_autoload_register(function ($className) {
    $className = ltrim($className, "\\");
    $match = array();
    preg_match('/^(.+)?([^\\\\]+)$/U', $className, $match);
    $classPath = ROOT_PATH . DIRECTORY_SEPARATOR . str_replace("\\", DIRECTORY_SEPARATOR, $match[1])
        . str_replace(array("\\", "_"), DIRECTORY_SEPARATOR, $match[2])
        . ".php";
    if (!include_once $classPath) {
        return false;
    }
    return true;
});
