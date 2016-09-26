<?php

$di = \Fw\DI::getInstance();

$di->set('request', '\Fw\Http\Request', true);
$di->set('response', '\Fw\Http\Response', true);
$di->set('buildList', function() {
    return new \Cm\Download\Api\BuildList\FolderBuildList(DOWNLOAD_ROOT);
});
