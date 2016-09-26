<?php

define('APP_DIR', realpath(__DIR__ . DIRECTORY_SEPARATOR . "..") . DIRECTORY_SEPARATOR);
define('LIB_DIR', APP_DIR);
define('DEBUG', false);

require_once LIB_DIR . 'lib/Fw/Loader.php';

$loader = new \Fw\Loader(LIB_DIR);
$loader->registerNamespaces(
    array(
        'Cm'          => "lib/Cm",
        'Fw'          => "lib/Fw",
        'Tests\Mocks' => "tests/mocks",
        'Tests\Fixtures' => "tests/fixtures",
    )
);
$loader->register();

require_once APP_DIR . '/vendor/autoload.php';
