<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'nomeCompleto' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Nome completo ou Razão Social'),
            'validations' => array(
                //'required' => \MapasCulturais\i::__('Seu nome completo ou jurídico deve ser informado.')
            )
        ),

        'documento' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('CPF ou CNPJ'),
//            'validations' => array(
//                'required' => \MapasCulturais\i::__('Seu CPF ou CNPJ deve ser informado.'),
//                'unique' => \MapasCulturais\i::__('Este documento já está registrado em nosso sistema.'),
//                'v::oneOf(v::cpf(), v::cnpj())' => \MapasCulturais\i::__('O número de documento informado é inválido.'),
//                'v::regex("#^(\d{2}(\.\d{3}){2}/\d{4}-\d{2})|(\d{3}\.\d{3}\.\d{3}-\d{2})$#")' => \MapasCulturais\i::__('Utilize o formato xxx.xxx.xxx-xx para CPF e xx.xxx.xxx/xxxx-xx para CNPJ.')
//            )
        ),

        'identidade' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Identidade (RG)')
        ),

        'estadoCivil' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Estado Civil'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Solteiro(a)' => \MapasCulturais\i::__('Solteiro(a)'),
                'Casado(a)' => \MapasCulturais\i::__('Casado(a)'),
                'Divorciado(a)' => \MapasCulturais\i::__('Divorciado(a)'),
                'Viúvo(a)' => \MapasCulturais\i::__('Viúvo(a)'),
                'Separado(a)' => \MapasCulturais\i::__('Separado(a)'),
                'União Estável' => \MapasCulturais\i::__('União Estável')
            )
        ),

        'escolaridade' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Escolaridade'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Ensino Fundamental Completo' => \MapasCulturais\i::__('Ensino Fundamental Completo'),
                'Ensino Fundamental Incompleto' => \MapasCulturais\i::__('Ensino Fundamental Incompleto'),
                'Ensino Médio Completo' => \MapasCulturais\i::__('Ensino Médio Completo'),
                'Ensino Médio Incompleto' => \MapasCulturais\i::__('Ensino Médio Incompleto'),
                'Ensino Superior Completo' => \MapasCulturais\i::__('Ensino Superior Completo'),
                'Ensino Superior Incompleto' => \MapasCulturais\i::__('Ensino Superior Incompleto'),
                'Pós-graduação (Especialização) Completa' => \MapasCulturais\i::__('Pós-graduação (Especialização) Completa'),
                'Pós-graduação (Especialização) Incompleta' => \MapasCulturais\i::__('Pós-graduação (Especialização) Incompleta'),
                'Pós-graduação (Mestrado) Completa' => \MapasCulturais\i::__('Pós-graduação (Mestrado) Completo'),
                'Pós-graduação (Mestrado) Incompleta' => \MapasCulturais\i::__('Pós-graduação (Mestrado) Incompleto'),
                'Pós-graduação (Doutorado) Completa' => \MapasCulturais\i::__('Pós-graduação (Doutorado) Completo'),
                'Pós-graduação (Doutorado) Incompleta' => \MapasCulturais\i::__('Pós-graduação (Doutorado) Incompleto')
            )
        ),

        'idade' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Idade'),
            'validations' => array(
                "v::intVal()->positive()" => \MapasCulturais\i::__("A idade/tempo deve ser um número positivo.")
            )
        ),

        'raca' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Raça/cor'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Branca' => \MapasCulturais\i::__('Branca'),
                'Preta' => \MapasCulturais\i::__('Preta'),
                'Amarela' => \MapasCulturais\i::__('Amarela'),
                'Parda' => \MapasCulturais\i::__('Parda'),
                'Indígena' => \MapasCulturais\i::__('Indígena')
            )
        ),

        'dataDeNascimento' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Data de Nascimento/Fundação'),
            'type' => 'date',
            'validations' => array(
                'v::date("Y-m-d")' => \MapasCulturais\i::__('Data inválida').'{{format}}',
            )
        ),

        'precisao' => array(
            'label' => \MapasCulturais\i::__('Localização'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Precisa' => \MapasCulturais\i::__('Precisa'),
                'Aproximada' => \MapasCulturais\i::__('Aproximada')
            )
        ),

        'localizacao' => array(
            'label' => \MapasCulturais\i::__('Localização'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Pública' => \MapasCulturais\i::__('Pública'),
                'Privada' => \MapasCulturais\i::__('Privada')
            )
        ),

        'genero' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Gênero'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Mulher Transexual' => \MapasCulturais\i::__('Mulher Transexual'),
                'Mulher' => \MapasCulturais\i::__('Mulher'),
                'Homem Transexual' => \MapasCulturais\i::__('Homem Transexual'),
                'Homem' => \MapasCulturais\i::__('Homem'),
                'Não Binário' => \MapasCulturais\i::__('Não Binário'),
                'Travesti' => \MapasCulturais\i::__('Travesti'),
                'Outras' => \MapasCulturais\i::__('Outras')
            )
        ),

        'orientacaoSexual' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Orientação Sexual'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informar'),
                'Assexual' => \MapasCulturais\i::__('Assexual'),
                'Bissexual' => \MapasCulturais\i::__('Bissexual'),
                'Heterossexual' => \MapasCulturais\i::__('Heterossexual'),
                'Homossexual' => \MapasCulturais\i::__('Homossexual'),
                'Transsexual' => \MapasCulturais\i::__('Transsexual'),
                'Transfeminino' => \MapasCulturais\i::__('Transfeminino'),
                'Transmasculino' => \MapasCulturais\i::__('Transmasculino'),
                'Pansexual' => \MapasCulturais\i::__('Pansexual'),
                'Outras' => \MapasCulturais\i::__('Outras')
            )
        ),

        'emailPublico' => array(
            'label' => \MapasCulturais\i::__('Email Público'),
            'validations' => array(
                'v::email()' => \MapasCulturais\i::__('O email público não é um email válido.')
            )
        ),

        'emailPrivado' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Email Privado'),
            'validations' => array(
                //'required' => \MapasCulturais\i::__('O email privado é obrigatório.'),
                'v::email()' => \MapasCulturais\i::__('O email privado não é um email válido.')
            )
        ),

        'telefonePublico' => array(
            'label' => \MapasCulturais\i::__('Telefone Público'),
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o telefone público no formato (xx) xxxx-xxxx.')
            )
        ),

        'telefone1' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Telefone 1'),
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o telefone 1 no formato (xx) xxxx-xxxx.')
            )
        ),


        'telefone2' => array(
            'private' => true,
            'label' => \MapasCulturais\i::__('Telefone 2'),
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o telefone 2 no formato (xx) xxxx-xxxx.')
            )
        ),

        'endereco' => array(
            'private' => function(){
                return !$this->publicLocation;
            },
            'label' => \MapasCulturais\i::__('Endereço'),
            'type' => 'text'
        ),

        'En_CEP' => [
            'label' => \MapasCulturais\i::__('CEP'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Nome_Logradouro' => [
            'label' => \MapasCulturais\i::__('Logradouro'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Num' => [
            'label' => \MapasCulturais\i::__('Número'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Complemento' => [
            'label' => \MapasCulturais\i::__('Complemento'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Bairro' => [
            'label' => \MapasCulturais\i::__('Bairro'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Municipio' => [
            'label' => \MapasCulturais\i::__('Município'),
            'private' => function(){
                return !$this->publicLocation;
            },
        ],
        'En_Estado' => [
            'label' => \MapasCulturais\i::__('Estado'),
            'private' => function(){
                return !$this->publicLocation;
            },
            'type' => 'select',

            'options' => array(
                'AC'=>'Acre',
                'AL'=>'Alagoas',
                'AP'=>'Amapá',
                'AM'=>'Amazonas',
                'BA'=>'Bahia',
                'CE'=>'Ceará',
                'DF'=>'Distrito Federal',
                'ES'=>'Espírito Santo',
                'GO'=>'Goiás',
                'MA'=>'Maranhão',
                'MT'=>'Mato Grosso',
                'MS'=>'Mato Grosso do Sul',
                'MG'=>'Minas Gerais',
                'PA'=>'Pará',
                'PB'=>'Paraíba',
                'PR'=>'Paraná',
                'PE'=>'Pernambuco',
                'PI'=>'Piauí',
                'RJ'=>'Rio de Janeiro',
                'RN'=>'Rio Grande do Norte',
                'RS'=>'Rio Grande do Sul',
                'RO'=>'Rondônia',
                'RR'=>'Roraima',
                'SC'=>'Santa Catarina',
                'SP'=>'São Paulo',
                'SE'=>'Sergipe',
                'TO'=>'Tocantins',
            )
        ],

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
        'googleplus' => array(
            'label' => \MapasCulturais\i::__('Google+'),
            'validations' => array(
                "v::url('plus.google.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            )
        ),

    ),
    'items' => array(
        1 => array( 'name' => \MapasCulturais\i::__('Individual' )),
        2 => array( 'name' => \MapasCulturais\i::__('Coletivo') ),
    )
);
