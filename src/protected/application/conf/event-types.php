<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'subTitle' => array(
            'label' => 'Sub-Título',
            'type' => 'text'
        ),
        'registrationInfo' => array(
            'label' => 'Inscripciones',
            'type' => 'text'
        ),
        'classificacaoEtaria' => array(
            'label' => 'Clasificación Etaria',
            'type' => 'select',
            'options' => array(
                 'Todo Público' => 'Todo Público',
                'Tercera Edad' => 'Tercera Edad',
                'Adultos' => 'Adultos',
                'Adolescente-Juvenil' => 'Adolescente-Juvenil',
                'Infantiles' => 'Infantiles'
            ),
            'validations' => array(
                'required' => "La Clasificación Etaria es obligatoria."
            )
        ),

        'telefonePublico' => array(
            'label' => 'Más información',
            'type' => 'string',
         //   'validations' => array(
         //       'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe el Teléfono Público en el formato (xx) xxxx-xxxx.'
         //   )
        ),

        'preco' => array(
            'label' => 'Precio'
        ),

        'traducaoLibras' => array(
            'label' => 'Traducción para Lenguaje de Señas',
            'type' => 'select',
            'options' => array(
                '' => 'No Informado',
                'Sim' => 'Sí',
                'Não' => 'No'
            )
        ),

        'descricaoSonora' => array(
            'label' => 'Audio descripción',
            'type' => 'select',
            'options' => array(
                '' => 'No Informado',
                'Sim' => 'Sí',
                'Não' => 'No'
            )
        ),

        'site' => array(
            'label' => 'Sitio',
          //  'validations' => array(
          //      "v::url()" => "La url informada no es válida. Deber comenzar con http://"
           // )
        ),
        'facebook' => array(
            'label' => 'Facebook',
        //    'validations' => array(
        //        "v::url('facebook.com')" => "La url informada no es válida. Deber comenzar con http://"
        //    )
        ),
        'twitter' => array(
            'label' => 'Twitter',
         //   'validations' => array(
         //       "v::url('twitter.com')" => "La url informada no es válida. Deber comenzar con http://"
         //   )
        ),
        'googleplus' => array(
            'label' => 'Google+',
         //   'validations' => array(
         //       "v::url('plus.google.com')" => "La url informada no es válida. Deber comenzar con http://"
         //   )
        ),

    ),
    'items' => array(
        1 =>  array('name' => 'Patrón'),
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
