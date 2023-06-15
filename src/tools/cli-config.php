<?php
require __DIR__ . '/../../public/bootstrap.php';
$app = MapasCulturais\App::i();

$helperSet = new \Symfony\Component\Console\Helper\HelperSet([
    'db' => $app->em->getConnection(),
    'em' => $app->em
]);