<?php

require_once 'bootstrap.php';
require_once 'config.php';

$api = new \Cm\Download\Api(DOWNLOAD_ROOT, DOWNLOAD_BASE_URL);
$api->handle();
