<?php
use MapasCulturais\i;

$texts = [
    'selecioneAgente' => i::__('Selecione um agente ativo para continuar.'),
    'erroTroca' => i::__('Não foi possível trocar o agente vinculado.'),
];

$app->applyHook('component(registration-print).texts', [&$texts]);

return $texts;
