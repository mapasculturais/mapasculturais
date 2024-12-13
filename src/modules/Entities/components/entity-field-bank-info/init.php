<?php

$definitions = MapasCulturais\Entities\Agent::getPropertiesMetadata();
$account_types = $app->config['module.registrationFieldTypes']['account_types'];
$bank_types = $app->config['module.registrationFieldTypes']['bank_types'];

$this->jsObject['config']['entityFieldConfig'] = [
    'accountTypes' => $account_types,
    'bankTypes' => $bank_types,
    'definations' => $definitions,
];