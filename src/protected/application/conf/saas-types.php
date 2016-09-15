<?php
/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
return array(
    'metadata' => array(
        'URL' => array(
          'label' => 'URL',
          'type' => 'text',
          'validations'=> array(
              'v::url()' => 'A URL informada é inválida.'
          )
        ),

        'entidades_habilitadas' => array(
            'label' => 'Entidades Habilitadas',
            'type' => 'multiselect',
            'options' => array(
                'Agentes',
                'Espaços',
                'Projetos',
                'Eventos',
                'Selos'
            )
        ),
        'cor_agentes' => array(
            'label' => 'Escolha uma cor hexadecimal. Ex: #FF1212',
        ),
        'cor_espacos' => array(
            'label' => 'Escolha uma cor hexadecimal. Ex: #FF1212',
        ),
        'cor_projetos' => array(
            'label' => 'Escolha uma cor hexadecimal. Ex: #FF1212',
        ),
        'cor_eventos' => array(
            'label' => 'Escolha uma cor hexadecimal. Ex: #FF1212',
        ),
        'cor_selos' => array(
            'label' => 'Cor: Selos',
              'validations' => [
//                  "v::regex('/([0-9ABCDEFabcdef]{3}|[0-9ABCDEFabcdef]{6})/')" => 'cor inválida'
              ]
        ),
        'filtro_space_meta_En_Estado' => array(
            'label' => 'Estado',
            'type' => 'multiselect',
            'serialize' => function($v) { return json_encode($v);},
            'unserialize' => function($v) { return json_decode($v);},
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
              'TO'=>'Tocantins'
            )
            /*,
            'validations' => array(
                "v::stringType()->in('AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO','OUT')" => 'O estado informado não existe.'
            )*/
        ),
        'filtro_agent_meta_En_Estado' => array(
            'label' => 'Estado',
            'type' => 'multiselect',
            'serialize' => function($v) { return json_encode($v);},
            'unserialize' => function($v) { return json_decode($v);},
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
              'TO'=>'Tocantins'
            )
            /*,
            'validations' => array(
                "v::stringType()->in('AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO','OUT')" => 'O estado informado não existe.'
            )*/
        ),
        'filtro_space_meta_type' => array(
            'label' => 'Tipo de espaço',
            'type' => 'multiselect',
            'serialize' => function($v) { return json_encode($v);},
            'unserialize' => function($v) { return json_decode($v);},
            'options' => (object) array(
                105 =>  'Antiquário',
                51  =>  'Arquivo Privado',
                50  =>  'Arquivo Público',
                106 =>  'Ateliê',
                112 =>  'Banca de jornal',
                200 =>  'Bens culturais de natureza material',
                2   =>  'Biblioteca Comunitária (incluí­dos os pontos de leitura)',
                23  =>  'Biblioteca Escolar',
                25  =>  'Biblioteca Universitária',
                26  =>  'Biblioteca Especializada',
                24  =>  'Biblioteca Nacional',
                21  =>  'Biblioteca Privada',
                20  =>  'Biblioteca Pública',
                25  =>  'Biblioteca Universitária',
                108 =>  'Casa de espetáculo',
                117 =>  'Casa do Patrimônio',
                116 =>  'Centro Comunitário',
                41  =>  'Centro Cultural Privado',
                40  =>  'Centro Cultural Público',
                85  =>  'Centro Espírita',
                71  =>  'Centro de Documentação Privado',
                70  =>  'Centro de Documentação Público',
                107 =>  'Centro de artesanato',
                10  =>  'Cine itinerante',
                11  =>  'Cineclube',
                91  =>  'Circo Fixo',
                90  =>  'Circo Itinerante',
                94  =>  'Circo Moderno',
                93  =>  'Circo Tradicional',
                111 =>  'Concha acústica',
                114 =>  'Creative Bureau',
                109 =>  'Danceteria',
                12  =>  'Drive-in',
                120 =>  'Espaço Mais Cultura',
                13  =>  'Espaço Público Para Projeção de Filmes',
                113 =>  'Espaço para Eventos',
                123 =>  'Espaço para apresentação de dança',
                110 =>  'Estúdio',
                122 =>  'Gafieira',
                100 =>  'Galeria de arte',
                84  =>  'Igreja',
                104 =>  'Lan-house',
                101 =>  'Livraria',
                82  =>  'Mesquitas',
                61  =>  'Museu Privado',
                60  =>  'Museu Público',
                501 =>  'Palco de Rua',
                115 =>  'Ponto de Leitura Afro',
                119 =>  'Praça dos esportes e da cultura',
                124 =>  'Rádio Comunitária',
                14  =>  'Sala de cinema',
                121 =>  'Sala de dança',
                102 =>  'Sebo',
                83  =>  'Sinagoga',
                31  =>  'Teatro Privado',
                30  =>  'Teatro Público',
                80  =>  'Templo',
                81  =>  'Terreiro',
                92  =>  'Terreno para Circo',
                118 =>  'Usina Cultural',
                103 =>  'Videolocadora'
            )
        ),
        'filtro_space_term_area' => array(
            'label' => 'Área de atuação',
            'type' => 'multiselect',
            'serialize' => function($v) { return json_encode($v);},
            'unserialize' => function($v) { return json_decode($v);},
            'options' => array(
                'Antropologia',
                'Arqueologia',
                'Arquitetura*Urbanismo',
                'Arquivo',
                'Arte Digital',
                'Arte de Rua',
                'Artes Visuais',
                'Artesanato',
                'Audiovisual',
                'Cinema',
                'Circo',
                'Comunicação',
                'Cultura Cigana',
                'Cultura Digital',
                'Cultura Estrangeira (imigrantes)',
                'Cultura Indígena',
                'Cultura LGBT',
                'Cultura Negra',
                'Cultura Popular',
                'Dança',
                'Design',
                'Direito Autoral',
                'Economia Criativa',
                'Educação',
                'Esporte',
                'Filosofia',
                'Fotografia',
                'Gastronomia',
                'Gestão Cultural',
                'História',
                'Jogos Eletrônicos',
                'Jornalismo',
                'Leitura',
                'Literatura',
                'Livro',
                'Meio Ambiente',
                'Moda',
                'Museu',
                'Mídias Sociais',
                'Música',
                'Novas Mídias',
                'Outros',
                'Patrimônio Imaterial',
                'Patrimônio Material',
                'Pesquisa',
                'Produção Cultural',
                'Rádio',
                'Saúde',
                'Sociologia',
                'Teatro',
                'Televisão',
                'Turismo'
            )
        ),
        'filtro_agent_term_area' => array(
            'label' => 'Área de atuação',
            'type' => 'multiselect',
            'serialize' => function($v) { return json_encode($v);},
            'unserialize' => function($v) { return json_decode($v);},
            'options' => array(
                'Antropologia',
                'Arqueologia',
                'Arquitetura*Urbanismo',
                'Arquivo',
                'Arte Digital',
                'Arte de Rua',
                'Artes Visuais',
                'Artesanato',
                'Audiovisual',
                'Cinema',
                'Circo',
                'Comunicação',
                'Cultura Cigana',
                'Cultura Digital',
                'Cultura Estrangeira (imigrantes)',
                'Cultura Indígena',
                'Cultura LGBT',
                'Cultura Negra',
                'Cultura Popular',
                'Dança',
                'Design',
                'Direito Autoral',
                'Economia Criativa',
                'Educação',
                'Esporte',
                'Filosofia',
                'Fotografia',
                'Gastronomia',
                'Gestão Cultural',
                'História',
                'Jogos Eletrônicos',
                'Jornalismo',
                'Leitura',
                'Literatura',
                'Livro',
                'Meio Ambiente',
                'Moda',
                'Museu',
                'Mídias Sociais',
                'Música',
                'Novas Mídias',
                'Outros',
                'Patrimônio Imaterial',
                'Patrimônio Material',
                'Pesquisa',
                'Produção Cultural',
                'Rádio',
                'Saúde',
                'Sociologia',
                'Teatro',
                'Televisão',
                'Turismo'
            )
        ),
        'filtro_event_term_linguagem' => array(
            'label' => 'Linguagem',
            'type' => 'multiselect',
            'serialize' => function($v) { return json_encode($v);},
            'unserialize' => function($v) { return json_decode($v);},
            'options' => array(
                  'Artes Circenses',
                  'Artes Integradas',
                  'Artes Visuais',
                  'Audiovisual',
                  'Cinema',
                  'Cultura Digital',
                  'Cultura Indígena',
                  'Cultura Tradicional',
                  'Curso ou Oficina',
                  'Dança',
                  'Exposição',
                  'Hip Hop',
                  'Livro e Literatura',
                  'Música Popular',
                  'Música Erudita',
                  'Palestra, Debate ou Encontro',
                  'Rádio',
                  'Teatro',
                  'Outros'
            )
        ),
        'texto_boasvindas' => array(
            'label' => 'Texto boas vindas',
            'type' => 'text'
        ),
        'texto_sobre' => array(
            'label' => 'Texto sobre',
            'type' => 'text'
        ),
        'zoom_default' => array(
            'label' => 'Zoom Padrão',
        ),
        'zoom_approximate' => array(
            'label' => 'Zoom Aproximado',
        ),
        'zoom_precise' => array(
            'label' => 'Zoom Preciso',
        ),
        'zoom_min' => array(
            'label' => 'Zoom Mínimo',
        ),
        'zoom_max' => array(
            'label' => 'Zoom Máximo',
        ),
        'latitude' => array(
            'label' => 'Latitude',
        ),
        'longitude' => array(
            'label' => 'Longitude',
        ),
        'filtro' => array(
            'label' => 'Filtros',
            'type' => 'text'
        ),
        'titulo' => array(
            'label' => 'Título'
        ),
        'titulo_projetos' => array(
            'label' => 'Título da entidade'
        ),
        'titulo_eventos' => array(
            'label' => 'Título da entidade'
        ),
        'titulo_agentes' => array(
            'label' => 'Título da entidade'
        ),
        'titulo_espacos' => array(
            'label' => 'Título da entidade'
        ),
        'titulo_selos' => array(
            'label' => 'Título da entidade'
        )
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
    'site' => array(
        'label' => 'Site',
        'type' => 'text',
        'validations'=> array(
            'v::url()' => 'A URL informada é inválida.'
        )
    )
     */
);
