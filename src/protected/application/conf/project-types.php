<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'introInscricoes' => array(
            'label' => 'Texto introdutório das inscrições',
            'type' => 'text'
        ),

        'registrationCategTitle' => array(
            'label' => 'Título das opções (ex: Categorias)',
        ),

        'registrationCategDescription' => array(
            'label' => 'Descrição das opções (ex: Selecione uma categoria)',
        ),

        'registrationLimitPerOwner' => array(
            'label' => 'Número máximo de inscrições por agente responsável',
            'validations' => array(
                "v::intVal()" => "O número máximo de inscrições por agente responsável deve ser um número inteiro"
            )
        ),

        'site' => array(
            'label' => 'Site',
            'validations' => array(
                "v::url()" => "A url informada é inválida."
            )
        ),

        'facebook' => array(
            'label' => 'Facebook',
            'validations' => array(
                "v::url('facebook.com')" => "A url informada é inválida."
            )
        ),
        'twitter' => array(
            'label' => 'Twitter',
            'validations' => array(
                "v::url('twitter.com')" => "A url informada é inválida."
            )
        ),
        'googleplus' => array(
            'label' => 'Google+',
            'validations' => array(
                "v::url('plus.google.com')" => "A url informada é inválida."
            )
        ),
    	
        'registrationSeals' => array(
                'label' => 'Selos',
                'serialize' => function($value) { return json_encode($value); },
                'unserialize' => function($value) { return json_decode($value); }
        ),

    ),
    'items' => array(
        1 =>  array( 'name' => "Festival"),
        2 =>  array( 'name' => "Encontro"),
        3 =>  array( 'name' => "Sarau"),
        4 =>  array( 'name' => "Reunião"),
        5 =>  array( 'name' => "Mostra"),
        6 =>  array( 'name' => "Convenção"),
        7 =>  array( 'name' => "Ciclo"),
        8 =>  array( 'name' => "Programa"),
        9 =>  array( 'name' => "Edital"),
        10 => array( 'name' => "Concurso"),
        11 => array( 'name' => "Exposição"),
        12 => array( 'name' => "Jornada"),
        13 => array( 'name' => "Exibição"),
        14 => array( 'name' => "Feira"),
        15 => array( 'name' => "Intercâmbio Cultural"),
        16 => array( 'name' => "Festa Popular"),
        17 => array( 'name' => "Festa Religiosa"),
        18 => array( 'name' => "Seminário"),
        19 => array( 'name' => "Congresso"),
        20 => array( 'name' => "Palestra"),
        21 => array( 'name' => "Simpósio"),
        22 => array( 'name' => "Fórum"),
        23 => array( 'name' => "Curso"),
        24 => array( 'name' => "Oficina"),
        25 => array( 'name' => "Jornada"),
        26 => array( 'name' => "Conferência Pública Setorial"),
        27 => array( 'name' => "Conferência Pública Nacional"),
        28 => array( 'name' => "Conferência Pública Estadual"),
        29 => array( 'name' => "Conferência Pública Municipal"),
        30 => array( 'name' => "Parada e Desfile Militar"),
        31 => array( 'name' => "Parada e Desfile Cívico"),
        32 => array( 'name' => "Parada e Desfile Festivo"),
        33 => array( 'name' => "Parada e Desfile Político"),
        34 => array( 'name' => "Parada e Desfile de Ações Afirmativas"),
        
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