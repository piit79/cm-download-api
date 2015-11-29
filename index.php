<?php

require_once 'app/bootstrap.php';

$api = new \Cm\Download\Api(DOWNLOAD_ROOT, DOWNLOAD_BASE_URL);
$api->handle();
