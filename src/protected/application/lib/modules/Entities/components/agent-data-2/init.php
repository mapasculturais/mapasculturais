<?php

$fields = [
    'name',
    'nomeCompleto',
    'cpf',
    'cnpj',
    'emailPublico',
    'emailPrivado',
    'telefonePublico',
    'telefonePrivado',
    'emailPrivado',

]; 

$app->applyHook('component(agent-data-2).fields', [&$fields]);

$this->jsObject['config']['agent-data-2'] = $fields;