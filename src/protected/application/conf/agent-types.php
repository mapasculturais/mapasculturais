<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'nomeCompleto' => array(
            'private' => true,
            'label' => 'Nombre completo o Razón Social',
            'validations' => array(
                //'required' => 'Seu nome completo ou jurídico deve ser informado.'
            )
        ),

        'documento' => array(
            'private' => true,
            'label' => 'CI o RUT',
//            'validations' => array(
//                'required' => 'Seu CPF ou CNPJ deve ser informado.',
//                'unique' => 'Este documento já está registrado em nosso sistema.',
//                'v::oneOf(v::cpf(), v::cnpj())' => 'O número de documento informado é inválido.',
//                'v::regex("#^(\d{2}(\.\d{3}){2}/\d{4}-\d{2})|(\d{3}\.\d{3}\.\d{3}-\d{2})$#")' => 'Utilize o formato xxx.xxx.xxx-xx para CPF e xx.xxx.xxx/xxxx-xx para CNPJ.'
//            )
        ),

        'idade' => array(
            'private' => true,
            'label' => 'Edad',
            'validations' => array(
                "v::intVal()->positive()" => "La edad/fecha debe ser um número positivo."
            )
        ),

        'raca' => array(
            'private' => true,
            'label' => 'Raza/color',
            'type' => 'select',
            'options' => array(
                '' => 'No informar',
                'Branca' => 'Blanca',
                'Preta' => 'Negra',
                'Amarela' => 'Amarilla',
                'Parda' => 'Parda',
                'Indígena' => 'Indígena'
            )
        ),

        'dataDeNascimento' => array(
            'private' => true,
            'label' => 'Fecha de nacimiento/fundación',
            'type' => 'date',
            'validations' => array(
                'v::date("Y-m-d")' => 'Fecha no válida {{format}}',
            )
        ),

        'precisao' => array(
            'label' => 'Localización',
            'type' => 'select',
            'options' => array(
                '' => 'No informar',
                'Precisa' => 'Precisa',
                'Aproximada' => 'Aproximada'
            )
        ),

        'localizacao' => array(
            'label' => 'Localización',
            'type' => 'select',
            'options' => array(
                '' => 'No informar',
                'Pública' => 'Pública',
                'Privada' => 'Privada'
            )
        ),

        'genero' => array(
            'private' => true,
            'label' => 'Género',
            'type' => 'select',
            'options' => array(
                '' => 'No informar',
                'Femenino' => 'Femenino',
                'Masculino' => 'Masculino',
                'Otro' => 'Otro'
            )
        ),

        'emailPublico' => array(
            'label' => 'Email Público',
            'validations' => array(
                'v::email()' => 'El email público no es un email válido.'
            )
        ),

        'emailPrivado' => array(
            'private' => true,
            'label' => 'Email Privado',
            'validations' => array(
                //'required' => 'O email privado es obligatorio.',
                'v::email()' => 'El email privado no es un email válido.'
            )
        ),

        'telefonePublico' => array(
            'label' => 'Teléfono Público',
            'type' => 'string',
            //'validations' => array(
            //    'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe el Teléfono Público en el formato (xx) xxxx-xxxx.'
            //)
        ),

        'telefone1' => array(
            'private' => true,
            'label' => 'Teléfono 1',
            'type' => 'string',
          //  'validations' => array(
          //      'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe el teléfono 1 en el formato (xx) xxxx-xxxx.'
          //  )
        ),


        'telefone2' => array(
            'private' => true,
            'label' => 'Teléfono 2',
            'type' => 'string',
          //  'validations' => array(
          //      'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe el teléfono 2 en el formato (xx) xxxx-xxxx.'
         //   )
        ),

        'endereco' => array(
            'private' => function(){
                return !$this->publicLocation;
            },
            'label' => 'Dirección',
            'type' => 'text'
        ),
                    
        'En_CEP' => [
            'label' => 'CP',
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Nome_Logradouro' => [
            'label' => 'Dirección',
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Num' => [
            'label' => 'Número',
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Complemento' => [
            'label' => 'Complemento',
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Bairro' => [
            'label' => 'Barrio',
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Municipio' => [
            'label' => 'Municipio',
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Estado' => [
            'label' => 'Departamento',
            'private' => function(){
                return !$this->publicLocation;
            },
            'type' => 'select',

            'options' => array(
                'Artigas'=>'Artigas',
                'Canelones'=>'Canelones',
                'Cerro Largo'=>'Cerro Largo',
                'Colonia'=>'Colonia',
                'Durazno'=>'Durazno',
                'Flores'=>'Flores',
                'Florida'=>'Florida',
                'Lavalleja'=>'Lavalleja',
                'Maldonado'=>'Maldonado',
                'Montevideo'=>'Montevideo',
                'Paysandú'=>'Paysandú',
                'Río Negro'=>'Río Negro',
                'Rivera'=>'Rivera',
                'Rocha'=>'Rocha',
                'Salto'=>'Salto',
                'San José'=>'San José',
                'Soriano'=>'Soriano',
                'Tacuarembó'=>'Tacuarembó',
                'Treinta y Tres'=>'Treinta y Tres',                
            )
        ],

        'site' => array(
            'label' => 'Sitio web',
         //   'validations' => array(
         //       "v::url()" => "La url informada no es válida. Deber comenzar con http://"
        //    )
        ),
        'facebook' => array(
            'label' => 'Facebook',
        //    'validations' => array(
         //       "v::url('facebook.com')" => "La url informada no es válida. Deber comenzar con http://"
        //    )
        ),
        'twitter' => array(
            'label' => 'Twitter',
         //   'validations' => array(
         //       "v::url('twitter.com')" => "La url informada no es válida. Deber comenzar con http://"
        //    )
        ),
        'googleplus' => array(
            'label' => 'Google+',
          //  'validations' => array(
          //      "v::url('plus.google.com')" => "La url informada no es válida. Deber comenzar con http://"
         //   )
        ),

    ),
    'items' => array(
        1 => array( 'name' => 'Individual' ),
        2 => array( 'name' => 'Colectivo' ),
    )
);
