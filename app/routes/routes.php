<?php

declare(strict_types=1);

$web = require_once 'web.php';
$api = require_once 'api.php';

return [
    ...$api,
    ...$web,
];
