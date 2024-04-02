<?php

declare(strict_types=1);

include_once dirname(__DIR__).'/app/src/Kernel.php';

\App\Kernel::execute();

require_once 'bootstrap.php';

$app->run();
