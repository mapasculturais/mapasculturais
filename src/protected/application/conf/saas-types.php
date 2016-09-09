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
        'filtro_uf' => array(
            'label' => 'Estado',
            'type' => 'multiselect',
            'options' => array(
                "'AC'",
                'AL',
                'AM',
                'AP',
                'BA',
                'CE',
                'DF',
                'ES',
                'GO',
                'MA',
                'MG',
                'MS',
                'MT',
                'PA',
                'PB',
                'PE',
                'PI',
                'PR',
                'RJ',
                'RN',
                'RO',
                'RR',
                'RS',
                'SC',
                'SE',
                'SP',
                'TO',
                'OUT'
            ),
            'validations' => array(
                "v::stringType()->in('AC','AL','AM','AP','BA','CE','DF','ES','GO','MA','MG','MS','MT','PA','PB','PE','PI','PR','RJ','RN','RO','RR','RS','SC','SE','SP','TO','OUT')" => 'O estado informado não existe.'
            )
        ),
        'filtro_espaco' => array(
            'label' => 'Tipo de espaço',
            'type' => 'multiselect',
            'options' => array(
                'Antiquário',
                'Arquivo Privado',
                'Arquivo Público',
                'Ateliê',
                'Banca de jornal',
                'Bens culturais de natureza material',
                'Biblioteca Privada',
                'Biblioteca Pública',
                'Casa de espetáculo',
                'Casa do Patrimônio',
                'Centro Comunitário',
                'Centro Cultural Privado',
                'Centro Cultural Público',
                'Centro de Documentação Privado',
                'Centro de Documentação Público',
                'Centro de artesanato',
                'Cine itinerante',
                'Cineclube',
                'Circo Fixo',
                'Circo Itinerante',
                'Concha acústica',
                'Creative Bureau',
                'Danceteria',
                'Drive-in',
                'Espaço Mais Cultura',
                'Espaço Público Para Projeção de Filmes',
                'Espaço para Eventos',
                'Espaço para apresentação de dança',
                'Estúdio',
                'Gafieira',
                'Galeria de arte',
                'Igreja',
                'Lan-house',
                'Livraria',
                'Mesquitas',
                'Museu Privado',
                'Museu Público',
                'Palco de Rua',
                'Ponto de Leitura Afro',
                'Praça dos esportes e da cultura',
                'Rádio Comunitária',
                'Sala de cinema',
                'Sala de dança',
                'Sebo',
                'Sinagoga',
                'Teatro Privado',
                'Teatro Público',
                'Templo',
                'Terreiro',
                'Terreno para Circo',
                'Usina Cultural',
                'Videolocadora'
            )
        ),
        'filtro_area_atuacao' => array(
            'label' => 'Área de atuação',
            'type' => 'multiselect',
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
            'label' => 'Título',
            'type' => 'text'
        ),
        'titulo_projetos' => array(
            'label' => 'Título da entidade',
            'type' => 'text'
        ),
        'titulo_eventos' => array(
            'label' => 'Título da entidade',
            'type' => 'text'
        ),
        'titulo_agentes' => array(
            'label' => 'Título da entidade',
            'type' => 'text'
        ),
        'titulo_espacos' => array(
            'label' => 'Título da entidade',
            'type' => 'text'
        ),
        'titulo_selos' => array(
            'label' => 'Título da entidade',
            'type' => 'text'
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
