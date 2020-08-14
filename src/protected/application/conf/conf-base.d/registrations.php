<?php

return [

    'registration.agentRelationsOptions' => array(
        'dontUse' => \MapasCulturais\i::__('Não utilizar'),
        'required' => \MapasCulturais\i::__('Obrigatório'),
        'optional' => \MapasCulturais\i::__('Opcional')
    ),

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
        'twitter'
    ),
    'registration.ownerDefinition' => array(
        'required' => true,
        'label' => \MapasCulturais\i::__('Agente responsável pela inscrição'),
        'agentRelationGroupName' => 'owner',
        'description' => \MapasCulturais\i::__('Agente individual (pessoa física) com os campos CPF, Data de Nascimento/Fundação, Gênero, Orientação Sexual, Raça/Cor, Email Privado e Telefone 1 obrigatoriamente preenchidos'),
        'type' => 1,
        'requiredProperties' => array('documento', 'raca', 'dataDeNascimento', 'genero', 'emailPrivado', 'telefone1')
    ),
    'registration.agentRelations' => array(
        array(
            'required' => false,
            'label' => \MapasCulturais\i::__('Instituição responsável'),
            'agentRelationGroupName' => 'instituicao',
            'description' => \MapasCulturais\i::__('Agente coletivo (pessoa jurídica) com os campos CNPJ, Data de Nascimento/Fundação, Email Privado e Telefone 1 obrigatoriamente preenchidos'),
            'type' => 2,
            'requiredProperties' => array('documento', 'dataDeNascimento', 'emailPrivado', 'telefone1')
        ),
        array(
            'required' => false,
            'label' => \MapasCulturais\i::__('Coletivo'),
            'agentRelationGroupName' => 'coletivo',
            'description' => \MapasCulturais\i::__('Agente coletivo sem CNPJ, com os campos Data de Nascimento/Fundação e Email Privado obrigatoriamente preenchidos'),
            'type' => 2,
            'requiredProperties' => array('dataDeNascimento', 'emailPrivado')
        )
    ),
    'registration.spaceRelations' => array(
        array(
            'required' => false,
            'label' => \MapasCulturais\i::__('Espaço Relacionado'),
            'description' => \MapasCulturais\i::__('Espaço Relacionado'),
            'type' => 2,
            'requiredProperties' => array('endereco', 'telefone1')
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
        'twitter'
    )
];