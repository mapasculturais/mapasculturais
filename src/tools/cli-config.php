<?php
use Doctrine\ORM\Tools\Console\ConsoleRunner;

require __DIR__ . '/../../public/bootstrap.php';

$app = MapasCulturais\App::i();

return ConsoleRunner::createHelperSet($app->em);