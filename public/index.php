<?php

declare(strict_types=1);

use App\Kernel;

include dirname(__DIR__).'/vendor/autoload.php';

Kernel::execute();

require_once 'bootstrap.php';

$app->run();
