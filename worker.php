<?php

declare(strict_types=1);

require 'vendor/autoload.php';
require_once __DIR__ . '/src/functions.php';
require_once __DIR__ . '/src/bootstrap.php';

/** @var Slim\App $app */
$app = require_once __DIR__ . '/app.php';

/** @var Spiral\RoadRunner\Http\PSR7WorkerInterface $psr7Worker */
$psr7Worker = $app->getContainer()->get(Spiral\RoadRunner\Http\PSR7WorkerInterface::class);

while ($req = $psr7Worker->waitRequest()) {
    try {
        $res = $app->handle($req);
        $psr7Worker->respond($res);
    } catch (Throwable $e) {
        $psr7Worker->getWorker()->error((string)$e);
    }
}
