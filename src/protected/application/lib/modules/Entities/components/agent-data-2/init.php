<?php

$fields = [
    'name',
    'nomeCompleto',
    'cpf',
    'cnpj',
    'emailPublico',
    'emailPrivado',
    'telefonePublico',
    'telefone1',
    'telefone2',
    'telefonePrivado',
    'emailPrivado',
    'emailPublico',

]; 

$app->applyHook('component(agent-data-2).fields', [&$fields]);

$this->jsObject['config']['agent-data-2'] = $fields;