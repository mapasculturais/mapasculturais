<?php

return [
    'registration.prefix' => env('REGISTRATION_PREFIX', 'on-'),

   'registration.proponentTypes' => [
    	\MapasCulturais\i::__('Pessoa Física'),
    	\MapasCulturais\i::__('MEI'),
   	\MapasCulturais\i::__('Coletivo'),
   	\MapasCulturais\i::__('Pessoa Jurídica'),
    ],

    'registration.proponentTypesToAgentsMap' => [
        'Pessoa Física' => 'owner',
        'MEI' => 'owner',
        'Coletivo' => 'coletivo',
        'Pessoa Jurídica' => 'coletivo',
    ],


    /*
    Timeout para o auto salvamento das inscrições (em milisegundos)
    */
    'registration.autosaveTimeout' => env('REGISTRATION_AUTOSAVE_INTERVAL', MINUTE_IN_SECONDS * 1000),

    'registration.agentRelationsOptions' => array(
        'dontUse' => \MapasCulturais\i::__('Não utilizar'),
        'required' => \MapasCulturais\i::__('Obrigatório'),
        'optional' => \MapasCulturais\i::__('Opcional')
    ),

    /*
    Array que define quais propriedades do reponsável serão exportados.

    ex: `["genero","raca"]`
    */
    'registration.reportOwnerProperties' => json_decode(env('REGISTRATION_REPORT_OWNER_PROPERTIES', '["name","genero","raca","documento"]')),

    'registration.propertiesToExport' => array(
        'id',
        'name',
        'nomeCompleto',
        'documento',
        'dataDeNascimento',
        'genero',
        'raca',
        'location',
        'endereco',
        'En_CEP',
        'En_Nome_Logradouro',
        'En_Num',
        'En_Complemento',
        'En_Bairro',
        'En_Municipio',
        'En_Estado',
        'telefone1',
        'telefone2',
        'telefonePublico',
        'emailPrivado',
        'emailPublico',
        'site',
        'googleplus',
        'facebook',
        'twitter',
        'fediverso'
    ),
    'registration.ownerDefinition' => array(
        'required' => true,
        'label' => \MapasCulturais\i::__('Agente responsável pela inscrição'),
        'agentRelationGroupName' => 'owner',
        'description' => \MapasCulturais\i::__('Agente individual (pessoa física) com os campos CPF, Data de Nascimento/Fundação, Gênero, Orientação Sexual, Raça/Cor, Email Privado e Telefone 1 obrigatoriamente preenchidos'),
        'type' => 1
    ),
    'registration.agentRelations' => array(
        array(
            'required' => false,
            'label' => \MapasCulturais\i::__('Coletivo'),
            'agentRelationGroupName' => 'coletivo',
            'description' => \MapasCulturais\i::__('Agente coletivo sem CNPJ, com os campos Data de Nascimento/Fundação e Email Privado obrigatoriamente preenchidos'),
            'type' => 2
        )
    ),
    'registration.spaceRelations' => array(
        array(
            'required' => false,
            'label' => \MapasCulturais\i::__('Espaço'),
            'description' => \MapasCulturais\i::__('Vincule um espaço a sua inscrição'),
            'type' => 2
        )
    ),
    'registration.spaceProperties' => array(
        'id',
        'name',
        'location',
        'endereco',
        'En_CEP',
        'En_Nome_Logradouro',
        'En_Num',
        'En_Complemento',
        'En_Bairro',
        'En_Municipio',
        'En_Estado',
        'telefone1',
        'telefone2',
        'telefonePublico',
        'emailPrivado',
        'emailPublico',
        'site',
        'googleplus',
        'facebook',
        'twitter',
        'fediverso'
    ),

    'registrations.distribution.dateString' => env('REGISTRATIONS_DISTRIBUTION_DATE_STRING', 'H:00'),
    'registrations.distribution.incrementString' => env('REGISTRATIONS_DISTRIBUTION_INCREMENT_STRING', '+1 hour'),
];
