<?php

use MapasCulturais\i;
use MapasCulturais\App;

$app = App::i();

$configs = json_encode($config);

$this->import('
    create-account
    mc-breadcrumb
');

$this->breadcrumb = [
    ['label'=> i::__('Voltar'), 'url' => $app->createUrl('auth')],
];
?>

<mc-breadcrumb></mc-breadcrumb>

<create-account config='<?= $configs; ?>'></create-account>