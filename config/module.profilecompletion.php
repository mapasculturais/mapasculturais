<?php
//Configurações Módulo ProfileCompletion
return [
    "module.ProfileCompletion" => [
        'enable' => env('MODULE_PROFILECOMPLETIN_ENABLE',true),
        'checkRequiredFieldsAgents' => env('MODULE_PROFILECOMPLETION_CHECK_REQUIRED_FIELDS_AGENTS',false)
    ]    
];