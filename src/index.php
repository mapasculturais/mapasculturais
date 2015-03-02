<?php
require_once __DIR__.'/protected/application/bootstrap.php';

MapasCulturais\App::i()->run();

//print_r(MapasCulturais\Loggers\DoctrineSQL\SlimLog::$uniqueQueries);
echo '<pre>';
print_r(MapasCulturais\App::i()->cache);