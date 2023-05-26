<?php

$sensitive_fields = [
    'agenteItinerante',
    'comunidadesTradicional',
    'comunidadesTradicionalOutros',
    'dataDeNascimento',
    'escolaridade',
    'genero',
    'orientacaoSexual',
    'pessoaDeficiente',
    'raca',
];
$fields = [
    'nomeCompleto',
    'nomeSocial',
    'cpf',
    'cnpj',
    'telefonePublico',
    'telefone1',
    'telefone2',
    'emailPrivado',
    'emailPublico',
];

$app->applyHook('component(agent-data).fields', [&$fields, &$sensitive_fields]);

$this->jsObject['config']['agent-data-1']['fields'] = $fields;
$this->jsObject['config']['agent-data-1']['sensitiveFields'] = $sensitive_fields;

