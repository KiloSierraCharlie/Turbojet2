<?php

require_once __DIR__.'/../vendor/autoload.php';

$settings = require __DIR__.'/../settings.php';
$app = require __DIR__.'/../src/app.php';

$app['debug'] = isset($settings['DEBUG']) && $settings['DEBUG'] === true;

$app->run();
