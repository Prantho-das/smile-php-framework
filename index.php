<?php 

require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/core/bootstrap.php';

$app = new Bootstrap([
    __DIR__ . '/routes/web.php',
    __DIR__ . '/routes/api.php',
]);
$app->init();