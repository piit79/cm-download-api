<?php

define('APP_DIR', realpath(__DIR__ . DIRECTORY_SEPARATOR . ".."));
define('LIB_DIR', APP_DIR . DIRECTORY_SEPARATOR . "lib");
define('DEBUG', false);

require_once 'config.php';
require_once LIB_DIR . '/Fw/Loader.php';

$loader = new \Fw\Loader(LIB_DIR);
$loader->registerNamespaces(
    array(
        "Cm" => "Cm",
        "Fw" => "Fw",
    )
);
$loader->register();
