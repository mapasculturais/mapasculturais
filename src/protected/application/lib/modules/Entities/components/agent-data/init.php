<?php

$fields = [
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

$app->applyHook('component(agent-data).fields', [&$fields]);

$this->jsObject['config']['agent-data'] = $fields;
