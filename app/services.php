<?php

$di = \Fw\DI::getInstance();

$di->set('request', '\Fw\Http\Request', true);
$di->set('response', '\Fw\Http\Response', true);
