<?php

require_once 'bootstrap.php';

$api = new \Cm\Download\Api("/home/error/src/cm", "http://cm.zz9.cz/get");
$api->handle();
