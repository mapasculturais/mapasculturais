<?php

use MapasCulturais\App;
use MapasCulturais\i;

$app = App::i();

$texts = [
    'salvando' => i::__('Salvando'),
    'removendo arquivo' => i::__('Removendo arquivo'),
];

$app->applyHook('component(entity).texts', [&$texts]);

return $texts;
