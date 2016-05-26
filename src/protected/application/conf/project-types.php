<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'introInscricoes' => array(
            'label' => 'Texto introductorio de las inscripciones',
            'type' => 'text'
        ),

        'registrationCategTitle' => array(
            'label' => 'Título de las opciones (ej: Categorías)',
        ),

        'registrationCategDescription' => array(
            'label' => 'Descripción de las opciones (ej: Seleccione una categoría)',
        ),

        'registrationLimitPerOwner' => array(
            'label' => 'Número máximo de inscripciones por agente responsable',
            'validations' => array(
                "v::intVal()" => "El número máximo de inscripciones por agente responsable debe ser un número entero"
            )
        ),

        'site' => array(
            'label' => 'Sitio',
           // 'validations' => array(
           //     "v::url()" => "La url informada no es válida. Deber comenzar con http://"
          //  )
        ),

        'facebook' => array(
            'label' => 'Facebook',
           // 'validations' => array(
           //     "v::url('facebook.com')" => "La url informada no es válida. Deber comenzar con http://"
          //  )
        ),
        'twitter' => array(
            'label' => 'Twitter',
           // 'validations' => array(
           //     "v::url('twitter.com')" => "La url informada no es válida. Deber comenzar con http://"
           // )
        ),
        'googleplus' => array(
            'label' => 'Google+',
           // 'validations' => array(
           //     "v::url('plus.google.com')" => "La url informada no es válida. Deber comenzar con http://"
           // )
        ),

    ),
    'items' => array(
        /*1 =>  array( 'name' => "Festival"),
        2 =>  array( 'name' => "Encuentro"),
        3 =>  array( 'name' => "Sala"),
        4 =>  array( 'name' => "Reunión"),
        5 =>  array( 'name' => "Muestra"),
        6 =>  array( 'name' => "Convención"),
        7 =>  array( 'name' => "Ciclo"),
        8 =>  array( 'name' => "Programa"),
        9 =>  array( 'name' => "Edital"),
        10 => array( 'name' => "Concurso"),
        11 => array( 'name' => "Exposición"),
        12 => array( 'name' => "Jornada"),
        13 => array( 'name' => "Exhibición"),
        14 => array( 'name' => "Feria"),
        15 => array( 'name' => "Intercambio Cultural"),
        16 => array( 'name' => "Festa Popular"),
        17 => array( 'name' => "Fiesta Religiosa"),
        18 => array( 'name' => "Seminario"),
        19 => array( 'name' => "Congreso"),
        20 => array( 'name' => "Charla"),
        21 => array( 'name' => "Simposio"),
        22 => array( 'name' => "Foro"),
        23 => array( 'name' => "Curso"),
        24 => array( 'name' => "Oficina"),
        25 => array( 'name' => "Jornada"),
        26 => array( 'name' => "Conferencia Pública Sectorial"),
        27 => array( 'name' => "Conferencia Pública Nacional"),
        28 => array( 'name' => "Conferencia Pública Departamental"),
        29 => array( 'name' => "Conferencia Pública Municipal"),
        30 => array( 'name' => "Desfile Militar"),
        31 => array( 'name' => "Desfile Cívico"),
        32 => array( 'name' => "Desfile Festivo"),
        33 => array( 'name' => "Desfile Político"),
        34 => array( 'name' => "Desfile de Acciones Afirmativas"),*/
    		1 =>  array( 'name' => "Ciclo"),
    		2 =>  array( 'name' => "Concurso"),
    		3 =>  array( 'name' => "Convención"),
    		4 =>  array( 'name' => "Convocatoria"),
    		5 =>  array( 'name' => "Encuentro"),
    		6 =>  array( 'name' => "Festival"),
    		7 =>  array( 'name' => "Jornada"),
    		8 =>  array( 'name' => "Llamado"),
    		9 =>  array( 'name' => "Muestra"),
    		10 => array( 'name' => "Programa"),
    		11 => array( 'name' => "Reunión"),
    		11 => array( 'name' => "Velada"),
        
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
            'v::url()' => 'La url informada no es válida.'
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
