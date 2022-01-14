<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'site' => array(
            'label' => \MapasCulturais\i::__('Site'),
            'validations' => array(
                "v::url()" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),

        'facebook' => array(
            'label' => \MapasCulturais\i::__('Facebook'),
            'validations' => array(
                "v::url('facebook.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),
        'twitter' => array(
            'label' => \MapasCulturais\i::__('Twitter'),
            'validations' => array(
                "v::url('twitter.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),

        'instagram' => array(
            'label' => \MapasCulturais\i::__('Instagram'),
            'validations' => array(
                "v::startsWith('@')" => \MapasCulturais\i::__("O usuário informado é inválido. Informe no formato @usuario e tente novamente")
            )
        ),
        'linkedin' => array(
            'label' => \MapasCulturais\i::__('Linkedin'),
            'validations' => array(
                "v::url('linkedin.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),
        'spotify' => array(
            'label' => \MapasCulturais\i::__('Spotify'),
            'validations' => array(
                "v::url('open.spotify.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),
        'youtube' => array(
            'label' => \MapasCulturais\i::__('YouTube'),
            'validations' => array(
                "v::url('youtube.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),
        'pinterest' => array(
            'label' => \MapasCulturais\i::__('Pinterest'),
            'validations' => array(
                "v::url('pinterest.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),

        'registrationSeals' => array(
                'label' => \MapasCulturais\i::__('Selos'),
                'serialize' => function($value) { return json_encode($value); },
                'unserialize' => function($value) { return json_decode($value); }
        ),
        'useSpaceRelation' => array(
            'label' => \MapasCulturais\i::__('Espaço Relacionado'),
            'type' => 'select',
            'options' => array(
                'dontUse' => \MapasCulturais\i::__('Não utilizar'),
                'required' => \MapasCulturais\i::__('Obrigatório'),
                'optional' => \MapasCulturais\i::__('Opcional')
            )
        ),

    ),
    'items' => array(
        1 =>  array( 'name' => \MapasCulturais\i::__("Festival")),
        2 =>  array( 'name' => \MapasCulturais\i::__("Encontro")),
        3 =>  array( 'name' => \MapasCulturais\i::__("Sarau")),
        4 =>  array( 'name' => \MapasCulturais\i::__("Reunião")),
        5 =>  array( 'name' => \MapasCulturais\i::__("Mostra")),
        6 =>  array( 'name' => \MapasCulturais\i::__("Convenção")),
        7 =>  array( 'name' => \MapasCulturais\i::__("Ciclo")),
        8 =>  array( 'name' => \MapasCulturais\i::__("Programa")),
        9 =>  array( 'name' => \MapasCulturais\i::__("Edital")),
        10 => array( 'name' => \MapasCulturais\i::__("Concurso")),
        11 => array( 'name' => \MapasCulturais\i::__("Exposição")),
        12 => array( 'name' => \MapasCulturais\i::__("Jornada")),
        13 => array( 'name' => \MapasCulturais\i::__("Exibição")),
        14 => array( 'name' => \MapasCulturais\i::__("Feira")),
        15 => array( 'name' => \MapasCulturais\i::__("Intercâmbio Cultural")),
        16 => array( 'name' => \MapasCulturais\i::__("Festa Popular")),
        17 => array( 'name' => \MapasCulturais\i::__("Festa Religiosa")),
        18 => array( 'name' => \MapasCulturais\i::__("Seminário")),
        19 => array( 'name' => \MapasCulturais\i::__("Congresso")),
        20 => array( 'name' => \MapasCulturais\i::__("Palestra")),
        21 => array( 'name' => \MapasCulturais\i::__("Simpósio")),
        22 => array( 'name' => \MapasCulturais\i::__("Fórum")),
        23 => array( 'name' => \MapasCulturais\i::__("Curso")),
        24 => array( 'name' => \MapasCulturais\i::__("Oficina")),
        25 => array( 'name' => \MapasCulturais\i::__("Jornada")),
        26 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Setorial")),
        27 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Nacional")),
        28 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Estadual")),
        29 => array( 'name' => \MapasCulturais\i::__("Conferência Pública Municipal")),
        30 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Militar")),
        31 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Cívico")),
        32 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Festivo")),
        33 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile Político")),
        34 => array( 'name' => \MapasCulturais\i::__("Parada e Desfile de Ações Afirmativas")),
        35 => ['name' => \MapasCulturais\i::__("Inscrições")],
        121 => ['name' => \MapasCulturais\i::__("Pesquisa")],
        122 => ['name' => \MapasCulturais\i::__("Consulta")]
    )
    /* EXEMPLOS DE METADADOS:

    'cnpj' => array(
        'label' => 'CNPJ',
        'type' => 'text',
        'validations' => array(
            'unique' => 'Este CNPJ já está cadastrado em nosso sistema.',
            'v::cnpj()' => 'O CNPJ é inválido.'
        )
    ),
    'cpf' => array(
        'label' => 'CPF',
        'type' => 'text',
        'validations' => array(
            'required' => 'Por favor, informe o CPF.',
            'v::cpf()' => 'O CPF é inválido.'
        )
    ),
    'radio' => array(
        'label' => 'Um exemplo de input radio',
        'type' => 'radio',
        'options' => array(
            'valor1' => 'Label do valor 1',
            'valor2' => 'Label do valor 2',
        ),
        'default_value' => 'valor1'
    ),
    'checkboxes' => array(
        'label' => 'Um exemplo de grupo de checkboxes',
        'type' => 'checkboxes',
        'options' => array(
            'valor1' => 'Label do Primeiro checkbox',
            'valor2' => 'Label do Primeiro checkbox'
        ),
        'default_value' => array(),
        'validations' => array(
            'v::arrayType()->notEmpty()' => 'Você deve marcar ao menos uma opção.'
        )
    ),
    'checkbox' => array(
        'label' => 'Um exemplo de campo booleano com checkbox.',
        'type' => 'checkbox',
        'input_value' => 1,
        'default_value' => 0
    ),
    'email' => array(
        'label' => 'Email público para contato',
        'type' => 'text',
        'validations'=> array(
            'v::email()' => 'O email informado é inválido.'
        )
    ),
    'site' => array(
        'label' => 'Site',
        'type' => 'text',
        'validations'=> array(
            'v::url()' => 'A URL informada é inválida.'
        )
    ),
    'estado' => array(
        'label' => 'Estado de Residência',
        'type' => 'select',
        'options' => array(
            ''   => '',
            'AC' => 'Acre',
            'AL' => 'Alagoas',
            'AM' => 'Amazonas',
            'AP' => 'Amapá',
            'BA' => 'Bahia',
            'CE' => 'Ceará',
            'DF' => 'Distrito Federal',
            'ES' => 'Espírito Santo',
            'GO' => 'Goiás',
            'MA' => 'Maranhão',
            'MG' => 'Minas Gerais',
            'MS' => 'Mato Grosso do Sul',
            'MT' => 'Mato Grosso',
            'PA' => 'Pará',
            'PB' => 'Paraíba',
            'PE' => 'Pernambuco',
            'PI' => 'Piauí',
            'PR' => 'Paraná',
            'RJ' => 'Rio de Janeiro',
            'RN' => 'Rio Grande do Norte',
            'RO' => 'Rondônia',
            'RR' => 'Roraima',
            'RS' => 'Rio Grande do Sul',
            'SC' => 'Santa Catarina',
            'SE' => 'Sergipe',
            'SP' => 'São Paulo',
            'TO' => 'Tocantins',
            ''   => '',
            'OUT'   => 'Resido Fora do Brasil'
        ),

        'validations' => array(
            "v::stringType()->in('AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO','OUT')" => 'O estado informado não existe.'
        )
    )
     */
);
