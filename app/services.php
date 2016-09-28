<?php

$di = \Fw\DI::getInstance();

$di->set('request', '\Fw\Http\Request', true);
$di->set('response', '\Fw\Http\Response', true);

switch (BUILD_LIST_ADAPTER) {
    case 'File':
        $di->set('buildList', function() {
            return new \Cm\Download\Api\BuildList\File(BUILD_LIST_FILE);
        });
        break;

    case 'Folder':
        $di->set('buildList', function() {
            return new \Cm\Download\Api\BuildList\Folder(DOWNLOAD_ROOT, DOWNLOAD_BASE_URL);
        });
        break;

    default:
        /** @var \Fw\Http\Response $response */
        $response = $di->get('response');
        $response->setup(500, \Fw\Http::CONTENT_TYPE_TEXT, '')->send();
}
