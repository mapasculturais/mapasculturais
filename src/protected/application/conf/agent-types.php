<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return [
    'metadata' => [
        'emailPublico' => [
            'label' => \MapasCulturais\i::__('Email Público'),
            'validations' => [
                'v::email()' => \MapasCulturais\i::__('O email público não é um email válido.')
            ]
        ],
        'emailPrivado' => [
            'private' => true,
            'label' => \MapasCulturais\i::__('Email Principal'),
            'validations' => [
                'required' => \MapasCulturais\i::__('O email Principal é obrigatório.'),
                'v::email()' => \MapasCulturais\i::__('O email Principal não é um email válido.')
            ]
        ],
        'telefonePublico' => [
            'label' => \MapasCulturais\i::__('Telefone'),
            'type' => 'string',
            'validations' => [
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o telefone público no formato (xx) xxxx-xxxx.')
            ]
        ],
        'telefone1' => [
            'private' => true,
            'label' => \MapasCulturais\i::__('Celular'),
            'type' => 'string',
            'validations' => [
                'required' => \MapasCulturais\i::__('O campo Telefone Principal deve ser preenchido.'),
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o Telefone Principal no formato (xx) xxxx-xxxx.')
            ]
        ],
        'endereco' => [
            'private' => function() {
                return !$this->publicLocation;
            },
            'label' => \MapasCulturais\i::__('Endereço'),
            'type' => 'text',
            'validations' => [
                'required' => \MapasCulturais\i::__('O campo Endereço deve ser preenchido.'),
            ]
        ],                    
        'En_CEP' => [
            'label' => \MapasCulturais\i::__('CEP'),
            'private' => function() {
                return !$this->publicLocation;
            },
            'validations' => [
                'required' => \MapasCulturais\i::__('O campo CEP deve ser preenchido.'),
            ]
        ],
        'En_Nome_Logradouro' => [
            'label' => \MapasCulturais\i::__('Logradouro'),
            'private' => function(){
                return !$this->publicLocation;
            },
            'validations' => [
                'required' => \MapasCulturais\i::__('O campo Logradouro deve ser preenchido.'),
            ]
        ],
        'En_Num' => [
            'label' => \MapasCulturais\i::__('Número'),
            'private' => function(){
                return !$this->publicLocation;
            },
            'validations' => [
                'required' => \MapasCulturais\i::__('O campo Número do Endereço deve ser preenchido.'),
            ]
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

            'options' => [
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
            ]
        ],
        'En_CE_Municipio' => [
            'label' => \MapasCulturais\i::__('Município'),
            'private' => function(){
                return !$this->publicLocation;
            },
            'type' => 'select',
            'options' => []
        ],
        'localizacao' => [
            'label' => \MapasCulturais\i::__('Localização'),
            'type' => 'select',
            'options' => [
                '' => \MapasCulturais\i::__('Não Informar'),
                'Pública' => \MapasCulturais\i::__('Pública'),
                'Privada' => \MapasCulturais\i::__('Privada')
            ]
        ],        

        'site' => [
            'label' => \MapasCulturais\i::__('Site'),
            'validations' => [
                "v::url()" => \MapasCulturais\i::__("A url informada é inválida.")
            ]
        ],
        'facebook' => [
            'label' => \MapasCulturais\i::__('Facebook'),
            'validations' => [
                "v::url('facebook.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ]
        ],
        'twitter' => [
            'label' => \MapasCulturais\i::__('Twitter'),
            'validations' => [
                "v::url('twitter.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ]
        ],
        'googleplus' => [
            'label' => \MapasCulturais\i::__('Google+'),
            'validations' => [
                "v::url('plus.google.com')" => \MapasCulturais\i::__("A url informada é inválida.")
            ]
        ],
        'instagram' => [
            'label' => \MapasCulturais\i::__('Instagram'),
            'validations' => [
                "v::startsWith('@')" => \MapasCulturais\i::__("O usuário informado é inválido. Informe no formato @usuario e tente novamente")
            ]
        ],
    ],

    'items' => [
        1 => [ 
                'name' => \MapasCulturais\i::__('Individual' ),
                'metadata' => [
                    'nomeCompleto' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Nome'),
                        'validations' => [
                            'required' => \MapasCulturais\i::__('O campo Nome deve ser preenchido.')
                        ]
                    ],
                    'nomeSocial' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe seu Nome Social'),
                        'type' => 'string'
                    ],
                    'nomeProfissional' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe seu Nome Profissional'),
                        'type' => 'string'
                    ],
                    'dataDeNascimento' => array(
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Data de Nascimento'),
                        'type' => 'date',
                        'validations' => array(
                            'v::date("Y-m-d")' => \MapasCulturais\i::__('Data inválida').'{{format}}',
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
                    'orientacaoSexual' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe sua Orientação Sexual'),
                        'type' => 'select',
                        'options' => [
                            '' => \MapasCulturais\i::__('Não Informar'),
                            'Assexual' => \MapasCulturais\i::__('Assexual'),
                            'Bissexual' => \MapasCulturais\i::__('Bissexual'),
                            'Heterossexual' => \MapasCulturais\i::__('Heterossexual'),
                            'Homossexual' => \MapasCulturais\i::__('Homossexual'),
                            'Transsexual' => \MapasCulturais\i::__('Transexual'),
                            'Transfeminino' => \MapasCulturais\i::__('Transfeminino'),
                            'Transmasculino' => \MapasCulturais\i::__('Transmasculino'),
                            'Pansexual' => \MapasCulturais\i::__('Pansexual'),
                            'Outra' => \MapasCulturais\i::__('Outra')
                        ]
                    ],
                    'estadoCivil' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe seu Estado Civil'),
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
                    ],
                    'escolaridade' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe sua Escolaridade'),
                        'type' => 'select',
                        'options' => array(
                            '' => \MapasCulturais\i::__('Não Informar'),
                            'Ensino Fundamental' => \MapasCulturais\i::__('Ensino Fundamental'),
                            'Ensino Fundamental Incompleto' => \MapasCulturais\i::__('Ensino Fundamental Incompleto'),
                            'Ensino Médio' => \MapasCulturais\i::__('Ensino Médio'),
                            'Ensino Médio Incompleto' => \MapasCulturais\i::__('Ensino Médio Incompleto'),
                            'Ensino Superior' => \MapasCulturais\i::__('Ensino Superior'),
                            'Ensino Superior Incompleto' => \MapasCulturais\i::__('Ensino Superior Incompleto'),
                            'Especialização' => \MapasCulturais\i::__('Especialização'),
                            'Especialização Incompleta' => \MapasCulturais\i::__('Especialização Incompleta'),
                            'Mestrado' => \MapasCulturais\i::__('Mestrado'),
                            'Mestrado Incompleto' => \MapasCulturais\i::__('Mestrado Incompleto'),
                            'Doutorado' => \MapasCulturais\i::__('Doutorado'),
                            'Doutorado Incompleto' => \MapasCulturais\i::__('Doutorado Incompleto')
                        )
                    ],
                    'documento' => array(
                        'private' => true,
                        'label' => \MapasCulturais\i::__('CPF'),
                        'validations' => [
                             'required' => \MapasCulturais\i::__('CPF deve ser informado.'),
                             'v::regex("#^(\d{3}\.\d{3}\.\d{3}-\d{2})$#")' => \MapasCulturais\i::__('Utilize o formato xxx.xxx.xxx-xx para CPF.')
                        ]
                    ),
                    'identidade' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe sua Identidade (RG)'),
                        'type' => 'text',
                         'validations' => [
                             'required' => \MapasCulturais\i::__('O campo Identidade (RG) deve ser preenchido.')
                         ]
                    ],
                    'expedicaoIdentidade' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe a Data de Expedição (RG)'),
                        'type' => 'date',
                        'validations' => [
                            'required' => \MapasCulturais\i::__('O campo Data de Expedição (RG) deve ser preenchido.'),
                            'v::date("Y-m-d")' => \MapasCulturais\i::__('Data inválida').'{{format}}',
                        ]
                    ],
                    'expedidorIdentidade' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe o Órgão Expedidor (RG)'),
                        'validations' => [
                            'required' => \MapasCulturais\i::__('O campo Órgão Expedidor (RG) deve ser preenchido.'),
                            'v::allOf(v::regex("#[a-zA-Z]/[a-zA-Z]{2}#"))' => \MapasCulturais\i::__('Por favor, informe o expedidor/unidade federativa, exemplo: SSP/CE , SSP/DF')
                        ]
                    ],
                    'nacionalidade' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe sua Nacionalidade'),
                        'type' => 'string'
                    ],
            
                    'naturalidade' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Informe sua Naturalidade'),
                        'type' => 'string'
                    ],
            
                ]    
            ],
        2 => [ 
                'name' => \MapasCulturais\i::__('Coletivo'),
                'metadata' => [
                    'razaoSocial' => [
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Razão Social'),
                        'validations' => [
                            'required' => \MapasCulturais\i::__('O campo Razão Social deve ser preenchido.')
                        ]
                    ],
                    'dataDeFundacao' => array(
                        'private' => true,
                        'label' => \MapasCulturais\i::__('Data de Fundação'),
                        'type' => 'date',
                        'validations' => array(
                            'v::date("Y-m-d")' => \MapasCulturais\i::__('Data inválida').'{{format}}',
                        )
                    ),
                    'cnpj' => array(
                        'private' => true,
                        'label' => \MapasCulturais\i::__('CNPJ'),
                        'validations' => [
                             'required' => \MapasCulturais\i::__('CNPJ deve ser informado.'),
                             'v::regex("#^(\d{2}(\.\d{3}){2}/\d{4}-\d{2})$#")' => \MapasCulturais\i::__('Utilize o formato xx.xxx.xxx/xxxx-xx para CNPJ.')
                        ]
                    ),
                ]

            ],
    ]
];
