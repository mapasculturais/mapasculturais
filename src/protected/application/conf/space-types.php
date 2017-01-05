<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'emailPublico' => array(
            'label' => \MapasCulturais\i::__('Email Público'),
            'validations' => array(
                'v::email()' => \MapasCulturais\i::__('O email público não é um email válido.')
            )
        ),

        'emailPrivado' => array(
            'label' => \MapasCulturais\i::__('Email Privado'),
            'validations' => array(
                'v::email()' => \MapasCulturais\i::__('O email privado não é um email válido.')
            ),
        	'private' => true
        ),

        'telefonePublico' => array(
            'label' => \MapasCulturais\i::__('Telefone Público'),
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o telefone público no formato (xx) xxxx-xxxx.')
            )
        ),

        'telefone1' => array(
            'label' => \MapasCulturais\i::__('Telefone 1'),
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o telefone 1 no formato (xx) xxxx-xxxx.')
            ),
        	'private' => true
        ),


        'telefone2' => array(
            'label' => \MapasCulturais\i::__('Telefone 2'),
            'type' => 'string',
            'validations' => array(
                'v::allOf(v::regex("#^\(\d{2}\)[ ]?\d{4,5}-\d{4}$#"), v::brPhone())' => \MapasCulturais\i::__('Por favor, informe o telefone 2 no formato (xx) xxxx-xxxx.')
            ),
        	'private' => true
        ),

        /*
        'virtual_fisico' => array(
            'label' => \MapasCulturais\i::__('Virtual ou físico'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Físico'),
                'virtual' => \MapasCulturais\i::__('Virtual')
            )
        ),
        */
        'acessibilidade' => array(
            'label' => \MapasCulturais\i::__('Acessibilidade'),
            'type' => 'select',
            'options' => array(
                '' => \MapasCulturais\i::__('Não Informado'),
                'Sim' => \MapasCulturais\i::__('Sim'),
                'Não' => \MapasCulturais\i::__('Não')
            )
        ),
        'acessibilidade_fisica' => array(
            'label' => \MapasCulturais\i::__('Acessibilidade física'),
            'type' => 'multiselect',
            'allowOther' => true,
            'allowOtherText' => \MapasCulturais\i::__('Outros'),
            'options' => array(
                \MapasCulturais\i::__('Banheiros adaptados'),
                \MapasCulturais\i::__('Rampa de acesso'),
                \MapasCulturais\i::__('Elevador'),
                \MapasCulturais\i::__('Sinalização tátil'),
                
                // vindos do sistema de museus.cultura.gov.br
                \MapasCulturais\i::__('Bebedouro adaptado'),
                \MapasCulturais\i::__('Cadeira de rodas para uso do visitante'),
                \MapasCulturais\i::__('Circuito de visitação adaptado'),
                \MapasCulturais\i::__('Corrimão nas escadas e rampas'),
                \MapasCulturais\i::__('Elevador adaptado'),
                \MapasCulturais\i::__('Rampa de acesso'),
                \MapasCulturais\i::__('Sanitário adaptado'),
                \MapasCulturais\i::__('Telefone público adaptado'),
                \MapasCulturais\i::__('Vaga de estacionamento exclusiva para deficientes'),
                \MapasCulturais\i::__('Vaga de estacionamento exclusiva para idosos')
            )
        ),
        'capacidade' => array(
            'label' => \MapasCulturais\i::__('Capacidade'),
            'validations' => array(
                "v::intVal()->positive()" => \MapasCulturais\i::__("A capacidade deve ser um número positivo.")
            )
        ),

        'endereco' => array(
            'label' => \MapasCulturais\i::__('Endereço'),
            'type' => 'text'
        ),
        

        'En_CEP' => [
            'label' => \MapasCulturais\i::__('CEP'),
        ],
        'En_Nome_Logradouro' => [
            'label' => \MapasCulturais\i::__('Logradouro'),
        ],
        'En_Num' => [
            'label' => \MapasCulturais\i::__('Número'),
        ],
        'En_Complemento' => [
            'label' => \MapasCulturais\i::__('Complemento'),
        ],
        'En_Bairro' => [
            'label' => \MapasCulturais\i::__('Bairro'),
        ],
        'En_Municipio' => [
            'label' => \MapasCulturais\i::__('Município'),
        ],
        'En_Estado' => [
            'label' => \MapasCulturais\i::__('Estado'),
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

        'horario' => array(
            'label' => \MapasCulturais\i::__('Horário de funcionamento'),
            'type' => 'text'
        ),

        'criterios' => array(
            'label' => \MapasCulturais\i::__('Critérios de uso do espaço'),
            'type' => 'text'
        ),

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
        )
    ),

/**
 * Equipamentos Culturais
 */

    'items' => array(
        \MapasCulturais\i::__('Espaços de Exibição de Filmes') => array(
            'range' => array(10,19),
            'items' => array(
                10 => array( 'name' => \MapasCulturais\i::__('Cine itinerante' )),
                11 => array( 'name' => \MapasCulturais\i::__('Cineclube' )),
                12 => array( 'name' => \MapasCulturais\i::__('Drive-in' )),
                13 => array( 'name' => \MapasCulturais\i::__('Espaço Público Para Projeção de Filmes') ),
                14 => array( 'name' => \MapasCulturais\i::__('Sala de cinema' )),
            )
        ),

        \MapasCulturais\i::__('Bibliotecas') => array(
            'range' => array(20,29),
            'items' => array(
                20 => array( 'name' => \MapasCulturais\i::__('Biblioteca Pública') ),
                21 => array( 'name' => \MapasCulturais\i::__('Biblioteca Privada' )),
                22 => array( 'name' => \MapasCulturais\i::__('Biblioteca Comunitária (incluí­dos os pontos de leitura)')),
                23 => array( 'name' => \MapasCulturais\i::__('Biblioteca Escolar')),
                24 => array( 'name' => \MapasCulturais\i::__('Biblioteca Nacional')),
                25 => array( 'name' => \MapasCulturais\i::__('Biblioteca Universitária')),
                26 => array( 'name' => \MapasCulturais\i::__('Biblioteca Especializada'))
            )
        ),

        \MapasCulturais\i::__('Teatros') => array(
            'range' => array(30,39),
            'items' => array(
                30 => array('name' => \MapasCulturais\i::__('Teatro Público')),
                31 => array('name' => \MapasCulturais\i::__('Teatro Privado'))
            )
        ),

        \MapasCulturais\i::__('Circos') => array(
            'range' => array(90,99),
            'items' => array(
                90 => array( 'name' => \MapasCulturais\i::__('Circo Itinerante' )),
                91 => array( 'name' => \MapasCulturais\i::__('Circo Fixo' )),
                92 => array( 'name' => \MapasCulturais\i::__('Terreno para Circo') ),
                93 => array( 'name' => \MapasCulturais\i::__('Circo Tradicional') ),
                94 => array( 'name' => \MapasCulturais\i::__('Circo Moderno' )),
            )
        ),

       \MapasCulturais\i::__('Centros Culturais') => array(
            'range' => array(40,49),
            'items' => array(
                40 => array( 'name' => \MapasCulturais\i::__('Centro Cultural Público' )),
                41 => array( 'name' => \MapasCulturais\i::__('Centro Cultural Privado' )),
            )
        ),

       \MapasCulturais\i::__('Arquivos') => array(
            'range' => array(50,59),
            'items' => array(
                50 => array( 'name' => \MapasCulturais\i::__('Arquivo Público') ),
                51 => array( 'name' => \MapasCulturais\i::__('Arquivo Privado' )),
            )
        ),

       \MapasCulturais\i::__('Museus') => array(
            'range' => array(60,69),
            'items' => array(
                60 => array( 'name' => \MapasCulturais\i::__('Museu Público') ),
                61 => array( 'name' => \MapasCulturais\i::__('Museu Privado' )),
            )
        ),

        \MapasCulturais\i::__('Demais Equipamentos Culturais') => array(
            'range' => array(100,199),
            'items' => array(
                100 => array( 'name' => \MapasCulturais\i::__('Galeria de arte') ),
                101 => array( 'name' => \MapasCulturais\i::__('Livraria' )),
                102 => array( 'name' => \MapasCulturais\i::__('Sebo' )),
                103 => array( 'name' => \MapasCulturais\i::__('Videolocadora') ),
                104 => array( 'name' => \MapasCulturais\i::__('Lan-house' )),
                105 => array( 'name' => \MapasCulturais\i::__('Antiquário' )),
                106 => array( 'name' => \MapasCulturais\i::__('Ateliê' )),
                107 => array( 'name' => \MapasCulturais\i::__('Centro de artesanato') ),
                108 => array( 'name' => \MapasCulturais\i::__('Casa de espetáculo' )),
                109 => array( 'name' => \MapasCulturais\i::__('Danceteria' )),
                110 => array( 'name' => \MapasCulturais\i::__('Estúdio' )),
                111 => array( 'name' => \MapasCulturais\i::__('Concha acústica' )),
                112 => array( 'name' => \MapasCulturais\i::__('Banca de jornal' )),
                113 => array( 'name' => \MapasCulturais\i::__('Espaço para Eventos' )),
                114 => array( 'name' => \MapasCulturais\i::__('Creative Bureau' )),
                115 => array( 'name' => \MapasCulturais\i::__('Ponto de Leitura Afro' )),
                116 => array( 'name' => \MapasCulturais\i::__('Centro Comunitário' )),
                117 => array( 'name' => \MapasCulturais\i::__('Casa do Patrimônio' )),
                125 => array( 'name' => \MapasCulturais\i::__('Ponto de Cultura' )),
                118 => array( 'name' => \MapasCulturais\i::__('Usina Cultural' )),
                119 => array( 'name' => \MapasCulturais\i::__('Praça dos esportes e da cultura') ),
                120 => array( 'name' => \MapasCulturais\i::__('Espaço Mais Cultura' )),
                121 => array( 'name' => \MapasCulturais\i::__('Sala de dança' )),
                122 => array( 'name' => \MapasCulturais\i::__('Gafieira' )),
                123 => array( 'name' => \MapasCulturais\i::__('Espaço para apresentação de dança' )),
                126 => array( 'name' => \MapasCulturais\i::__('Centro cultural itinerante' )),
                127 => array( 'name' => \MapasCulturais\i::__('Trio elétrico' )),
                128 => array( 'name' => \MapasCulturais\i::__('Clube social' )),
                129 => array( 'name' => \MapasCulturais\i::__('Centro de tradições') ),
                130 => array( 'name' => \MapasCulturais\i::__('Sala Multiuso' )),
                124 => array( 'name' => \MapasCulturais\i::__('Rádio Comunitária' )),
                131 => array( 'name' => \MapasCulturais\i::__('Audioteca' )),
                132 => array( 'name' => \MapasCulturais\i::__('Centro de Artes e Esportes Unificados - CEUs' )),
                133 => array( 'name' => \MapasCulturais\i::__('Coreto' )),
                134 => array( 'name' => \MapasCulturais\i::__('Ginásio Poliesportivo') ),
                135 => array( 'name' => \MapasCulturais\i::__('Sala de Leitura' )),
                
                199 => array( 'name' => \MapasCulturais\i::__('Outros Equipamentos Culturais' )), // adicionado na importação dos dados do Ceará para receber as endidades do tipo "equipamento"
            )
        ),

       \MapasCulturais\i::__('Centros de Documentação') => array(
            'range' => array(70,79),
            'items' => array(
                70 => array( 'name' => \MapasCulturais\i::__('Centro de Documentação Público') ),
                71 => array( 'name' => \MapasCulturais\i::__('Centro de Documentação Privado' )),
            )
        ),

       \MapasCulturais\i::__('Espaços Religiosos') => array(
            'range' => array(80,89),
            'items' => array(
                80 => array( 'name' => \MapasCulturais\i::__('Templo' )),
                81 => array( 'name' => \MapasCulturais\i::__('Terreiro' )),
                82 => array( 'name' => \MapasCulturais\i::__('Mesquitas' )),
                83 => array( 'name' => \MapasCulturais\i::__('Sinagoga' )),
                84 => array( 'name' => \MapasCulturais\i::__('Igreja' )),
                85 => array( 'name' => \MapasCulturais\i::__('Centro Espírita') ),
              )
        ),

/**
 * Espaços de Formação Cultural
 */

       \MapasCulturais\i::__('Instituições Públicas de Ensino Regular') => array(
            'range' => array(300,399),
            'items' => array(
                300 => array( 'name' => \MapasCulturais\i::__('Instituição Pública de Ensino Regular Federal' )),
                301 => array( 'name' => \MapasCulturais\i::__('Instituição Pública de Ensino Regular Estadual' )),
                302 => array( 'name' => \MapasCulturais\i::__('Instituição Pública de Ensino Regular Municipal' )),
                303 => array( 'name' => \MapasCulturais\i::__('Instituição Pública de Ensino Regular Distrital' )),
              )
        ),

       \MapasCulturais\i::__(' Instituições Privadas de Ensino Regular') => array(
            'range' => array(400,499),
            'items' => array(
                400 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Particular' )),
                401 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Comunitária' )),
                402 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Confessional' )),
                403 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Filantrópica' )),
              )
        ),

       \MapasCulturais\i::__('Instituições Públicas exclusivamente voltada para formação artistica e cultural') => array(
            'range' => array(601,699),
            'items' => array(
                601 => array( 'name' => \MapasCulturais\i::__('Instituição Pública Federal exclusivamente voltada para formação artistica e cultural' )),
                602 => array( 'name' => \MapasCulturais\i::__('Instituição Pública Estadual exclusivamente voltada para formação artistica e cultural' )),
                603 => array( 'name' => \MapasCulturais\i::__('Instituição Pública Municipal exclusivamente voltada para formação artistica e cultural' )),
                604 => array( 'name' => \MapasCulturais\i::__('Instituição Pública Distrital exclusivamente voltada para formação artistica e cultural' )),
              )
        ),

       \MapasCulturais\i::__('Instituições Privadas exclusivamente voltada para formação artistica e cultural') => array(
            'range' => array(700,799),
            'items' => array(
                700 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Particular exclusivamente voltada para formação artistica e cultural' )),
                701 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Comunitária exclusivamente voltada para formação artistica e cultural' )),
                702 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Confessional exclusivamente voltada para formação artistica e cultural' )),
                703 => array( 'name' => \MapasCulturais\i::__('Instituição Privada Filantrópica exclusivamente voltada para formação artistica e cultural' )),
              )
        ),

       \MapasCulturais\i::__('Escolas livres') => array(
            'range' => array(800,899),
            'items' => array(
                800 => array( 'name' => \MapasCulturais\i::__('Escola livre de Artes Cênicas' )),
                801 => array( 'name' => \MapasCulturais\i::__('Escola livre de Artes Visuais' )),
                802 => array( 'name' => \MapasCulturais\i::__('Escola livre de Audiovisual' )),
                803 => array( 'name' => \MapasCulturais\i::__('Escola livre de Hip Hop' )),
                804 => array( 'name' => \MapasCulturais\i::__('Escola livre de Cultura Digital' )),
                805 => array( 'name' => \MapasCulturais\i::__('Escola livre de Música' )),
                806 => array( 'name' => \MapasCulturais\i::__('Escola livre de Cultura Popular' )),
                807 => array( 'name' => \MapasCulturais\i::__('Escola livre de Gestão Cultural' )),
                808 => array( 'name' => \MapasCulturais\i::__('Escola livre de Pontinhos de cultura') ),
                809 => array( 'name' => \MapasCulturais\i::__('Escola livre de Patrimônio' )),
                810 => array( 'name' => \MapasCulturais\i::__('Escola livre de Design' )),
              )
        ),

/**
 * Patrimônios Culturais
 */

        \MapasCulturais\i::__('Bens culturais de natureza material') => array(
            'range' => array(200, 299),
            'items' => array(
                200 => array( 'name' => \MapasCulturais\i::__('Bens culturais de natureza material' )),
                201 => array( 'name' => \MapasCulturais\i::__('Bem Imóvel' )),
                202 => array( 'name' => \MapasCulturais\i::__('Bem Arqueológico' )),
                203 => array( 'name' => \MapasCulturais\i::__('Bem Paisagístico' )),
                204 => array( 'name' => \MapasCulturais\i::__('Bem Móvel ou Integrado' )),
                205 => array( 'name' => \MapasCulturais\i::__('Sitio Histórico' )),
                206 => array( 'name' => \MapasCulturais\i::__('Documentação' )),
                207 => array( 'name' => \MapasCulturais\i::__('Coleções' )),
                
                210 => array( 'name' => \MapasCulturais\i::__('Bens culturais de natureza imaterial' )), // adicionado na importação dos dados do Ceará para receber as endidades do tipo "patrimonio-imaterial"
                
                299 => array( 'name' => \MapasCulturais\i::__('Outros' )) // adicionado na importação dos dados do Ceará para receber as endidades do tipo "post"
            )
        ),

        \MapasCulturais\i::__('Temporário') => array(
            'range' => array(500,600),
            'items' => array(
                501 => array( 'name' => \MapasCulturais\i::__('Palco de Rua' )),
            )
        ),
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
