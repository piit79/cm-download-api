<?php

require_once 'lib/Fw/Loader.php';

define('APP_DIR', realpath(__DIR__));
define('LIB_DIR', APP_DIR . DIRECTORY_SEPARATOR . "lib");
define('DEBUG', false);

$loader = new \Fw\Loader(LIB_DIR);
$loader->registerNamespaces(
    array(
        "Cm" => "Cm",
        "Fw" => "Fw",
    )
);
$loader->register();
