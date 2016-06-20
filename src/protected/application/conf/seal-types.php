<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'nomeCompleto' => array(
            'private' => true,
            'label' => 'Nome completo ou Razão Social',
            'validations' => array(
                //'required' => 'Seu nome completo ou jurídico deve ser informado.'
            )
        ),

        'documento' => array(
            'private' => true,
            'label' => 'CPF ou CNPJ',
//            'validations' => array(
//                'required' => 'Seu CPF ou CNPJ deve ser informado.',
//                'unique' => 'Este documento já está registrado em nosso sistema.',
//                'v::oneOf(v::cpf(), v::cnpj())' => 'O número de documento informado é inválido.',
//                'v::regex("#^(\d{2}(\.\d{3}){2}/\d{4}-\d{2})|(\d{3}\.\d{3}\.\d{3}-\d{2})$#")' => 'Utilize o formato xxx.xxx.xxx-xx para CPF e xx.xxx.xxx/xxxx-xx para CNPJ.'
//            )
        ),

        'idade' => array(
            'private' => true,
            'label' => 'Idade',
            'validations' => array(
                "v::intVal()->positive()" => "A idade/tempo deve ser um número positivo."
            )
        ),

        'raca' => array(
            'private' => true,
            'label' => 'Raça/cor',
            'type' => 'select',
            'options' => array(
                '' => 'Não Informar',
                'Branca' => 'Branca',
                'Preta' => 'Preta',
                'Amarela' => 'Amarela',
                'Parda' => 'Parda',
                'Indígena' => 'Indígena'
            )
        ),

        'dataDeNascimento' => array(
            'private' => true,
            'label' => 'Data de Nascimento/Fundação',
            'type' => 'date',
            'validations' => array(
                'v::date("Y-m-d")' => 'Data inválida {{format}}',
            )
        ),

        'precisao' => array(
            'label' => 'Localização',
            'type' => 'select',
            'options' => array(
                '' => 'Não Informar',
                'Precisa' => 'Precisa',
                'Aproximada' => 'Aproximada'
            )
        ),

        'localizacao' => array(
            'label' => 'Localização',
            'type' => 'select',
            'options' => array(
                '' => 'Não Informar',
                'Pública' => 'Pública',
                'Privada' => 'Privada'
            )
        ),

        'genero' => array(
            'private' => true,
            'label' => 'Gênero',
            'type' => 'select',
            'options' => array(
                '' => 'Não Informar',
                'Mulher Transexual' => 'Mulher Transexual',
                'Mulher' => 'Mulher',
                'Homem Transexual' => 'Homem Transexual',
                'Homem' => 'Homem',
                'Não Binário' => 'Não Binário',
                'Travesti' => 'Travesti',
                'Outras' => 'Outras'
            )
        ),

        'orientacaoSexual' => array(
            'private' => true,
            'label' => 'Orientação Sexual',
            'type' => 'select',
            'options' => array(
                '' => 'Não Informar',
                'Heterossexual' => 'Heterossexual',
                'Lésbica' => 'Lésbica',
                'Gay' => 'Gay',
                'Bissexual' => 'Bissexual',
                'Assexual' => 'Assexual',
                'Outras' => 'Outras'
            )
        ),

        'emailPublico' => array(
            'label' => 'Email Público',
            'validations' => array(
                'v::email()' => 'O email público não é um email válido.'
            )
        ),

        'emailPrivado' => array(
            'private' => true,
            'label' => 'Email Privado',
            'validations' => array(
                //'required' => 'O email privado é obrigatório.',
                'v::email()' => 'O email privado não é um email válido.'
            )
        ),

        'telefonePublico' => array(
            'label' => 'Telefone Público',
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe o telefone público no formato (xx) xxxx-xxxx.'
            )
        ),

        'telefone1' => array(
            'private' => true,
            'label' => 'Telefone 1',
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe o telefone 1 no formato (xx) xxxx-xxxx.'
            )
        ),


        'telefone2' => array(
            'private' => true,
            'label' => 'Telefone 2',
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => 'Por favor, informe o telefone 2 no formato (xx) xxxx-xxxx.'
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

    ),
    'items' => array(
        1 => array( 'name' => 'Individual' ),
        2 => array( 'name' => 'Coletivo' ),
    )
);

/*		
		
	'items' => array(
		0 => array( 'name' => 'Infinita' ),
        1 => array( 'name' => 'Dias' ),
        2 => array( 'name' => 'Semanas' ),
    	3 => array( 'name' => 'Meses' ),
    	4 => array( 'name' => 'Anos' ),
	)
);*/