<?php

require_once 'lib/CmDownloadApi.php';

$api = new CmDownloadApi("/home/error/src/cm", "http://cm.zz9.cz/get");
$api->handle();
