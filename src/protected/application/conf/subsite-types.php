<?php

/**
 * See https://github.com/Respect/Validation to know how to write validations
 */
$color_validation = "v::regex('/^#([0-9ABCDEFabcdef]{3}|[0-9ABCDEFabcdef]{6})/')";

$metadata_config_estado = [
    'label' => 'Estado',
    'type' => 'multiselect',
    'serialize' => function($v) {
        return json_encode($v);
    },
    'unserialize' => function($v) {
        return json_decode($v);
    },
    'options' => [
        'AC' => 'Acre',
        'AL' => 'Alagoas',
        'AP' => 'Amapá',
        'AM' => 'Amazonas',
        'BA' => 'Bahia',
        'CE' => 'Ceará',
        'DF' => 'Distrito Federal',
        'ES' => 'Espírito Santo',
        'GO' => 'Goiás',
        'MA' => 'Maranhão',
        'MT' => 'Mato Grosso',
        'MS' => 'Mato Grosso do Sul',
        'MG' => 'Minas Gerais',
        'PA' => 'Pará',
        'PB' => 'Paraíba',
        'PR' => 'Paraná',
        'PE' => 'Pernambuco',
        'PI' => 'Piauí',
        'RJ' => 'Rio de Janeiro',
        'RN' => 'Rio Grande do Norte',
        'RS' => 'Rio Grande do Sul',
        'RO' => 'Rondônia',
        'RR' => 'Roraima',
        'SC' => 'Santa Catarina',
        'SP' => 'São Paulo',
        'SE' => 'Sergipe',
        'TO' => 'Tocantins'
    ]
];

$metadata_config_color = [
    'label' => 'Cor da entidade',
    'validations' => [
        $color_validation => 'cor inválida'
    ]
];


$metadata_config_area = [
    'label' => 'Área de atuação',
    'type' => 'multiselect',
    'serialize' => function($v) {
        return json_encode($v);
    },
    'unserialize' => function($v) {
        return json_decode($v);
    },
    'options' => [
        /* @TODO: incluir arquivo taxonomies.php e pegar os termos de lá */

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
    ]
];

return array(
    'metadata' => array(
        'URL' => array(
            'label' => 'URL',
            'type' => 'text',
            'validations' => array(
                'v::url()' => 'A URL informada é inválida.'
            )
        ),
        'entidades_habilitadas' => array(
            'label' => 'Entidades Habilitadas',
            'type' => 'multiselect',
            'options' => array(
                'Agents',
                'Spaces',
                'Projects',
                'Events',
                'Opportunities'
            )
        ),
        'agents_color'        => $metadata_config_color,
        'spaces_color'        => $metadata_config_color,
        'projects_color'      => $metadata_config_color,
        'events_color'        => $metadata_config_color,
        'opportunities_color' => $metadata_config_color,
        'seals_color'         => $metadata_config_color,
        'cor_intro'           => $metadata_config_color,
        'cor_dev'             => $metadata_config_color,

        'filtro_space_meta_En_Estado' => $metadata_config_estado,

        'filtro_space_meta_En_Municipio' => [
            'label' => 'Município',
            'type' => 'tag',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return json_decode($v);
            },
        ],
        'filtro_space_meta_En_Bairro' => [
            'label' => 'Bairro',
            'type' => 'tag',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return json_decode($v);
            },
        ],

        'filtro_agent_meta_En_Estado' => $metadata_config_estado,

        'filtro_agent_meta_En_Municipio' => [
            'label' => 'Município',
            'type' => 'tag',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return json_decode($v);
            },
        ],
        'filtro_agent_meta_En_Bairro' => [
            'label' => 'Bairro',
            'type' => 'tag',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return json_decode($v);
            },
        ],

        'filtro_space_meta_type' => array(
            'label' => 'Tipo de espaço',
            'type' => 'multiselect',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return json_decode($v);
            },
            'options' => (object) array(
                105 => 'Antiquário',
                51 => 'Arquivo Privado',
                50 => 'Arquivo Público',
                106 => 'Ateliê',
                112 => 'Banca de jornal',
                200 => 'Bens culturais de natureza material',
                22 => 'Biblioteca Comunitária (incluí­dos os pontos de leitura)',
                23 => 'Biblioteca Escolar',
                25 => 'Biblioteca Universitária',
                26 => 'Biblioteca Especializada',
                24 => 'Biblioteca Nacional',
                21 => 'Biblioteca Privada',
                20 => 'Biblioteca Pública',
                108 => 'Casa de espetáculo',
                117 => 'Casa do Patrimônio',
                116 => 'Centro Comunitário',
                41 => 'Centro Cultural Privado',
                40 => 'Centro Cultural Público',
                85 => 'Centro Espírita',
                71 => 'Centro de Documentação Privado',
                70 => 'Centro de Documentação Público',
                107 => 'Centro de artesanato',
                10 => 'Cine itinerante',
                11 => 'Cineclube',
                91 => 'Circo Fixo',
                90 => 'Circo Itinerante',
                94 => 'Circo Moderno',
                93 => 'Circo Tradicional',
                111 => 'Concha acústica',
                114 => 'Creative Bureau',
                109 => 'Danceteria',
                12 => 'Drive-in',
                120 => 'Espaço Mais Cultura',
                13 => 'Espaço Público Para Projeção de Filmes',
                113 => 'Espaço para Eventos',
                123 => 'Espaço para apresentação de dança',
                110 => 'Estúdio',
                122 => 'Gafieira',
                100 => 'Galeria de arte',
                84 => 'Igreja',
                104 => 'Lan-house',
                101 => 'Livraria',
                82 => 'Mesquitas',
                61 => 'Museu Privado',
                60 => 'Museu Público',
                137 => 'Núcleos de Produção Digital',
                501 => 'Palco de Rua',
                115 => 'Ponto de Leitura Afro',
                136 => 'Pontos de Memória',
                119 => 'Praça dos esportes e da cultura',
                124 => 'Rádio Comunitária',
                14 => 'Sala de cinema',
                121 => 'Sala de dança',
                102 => 'Sebo',
                83 => 'Sinagoga',
                31 => 'Teatro Privado',
                30 => 'Teatro Público',
                80 => 'Templo',
                81 => 'Terreiro',
                92 => 'Terreno para Circo',
                118 => 'Usina Cultural',
                103 => 'Videolocadora'
            )
        ),
        'filtro_space_term_area' => $metadata_config_area,
        'filtro_agent_term_area' => $metadata_config_area,

        'filtro_event_term_linguagem' => array(
            'label' => 'Linguagem',
            'type' => 'multiselect',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return json_decode($v);
            },
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
        'dict' => [
            'label' => 'Textos configurados',
            'type' => 'array',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return (array) json_decode($v);
            },
        ],
        'user_filters__event' => [
            'label' => 'Filtros dispoíveis por Evento',
            'type' => 'array',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return (array) json_decode($v);
            },
        ],
        'user_filters__space' => [
            'label' => 'Filtros dispoíveis por Espaço',
            'type' => 'array',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return (array) json_decode($v);
            },
        ],
        'user_filters__agent' => [
            'label' => 'Filtros dispoíveis por Agente',
            'type' => 'array',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return (array) json_decode($v);
            },
        ],
        'user_filters__project' => [
            'label' => 'Filtros dispoíveis por Projeto',
            'type' => 'array',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return (array) json_decode($v);
            },
        ],
        'user_filters__opportunity' => [
            'label' => 'Filtros dispoíveis por Oportunidade',
            'type' => 'array',
            'serialize' => function($v) {
                return json_encode($v);
            },
            'unserialize' => function($v) {
                return (array) json_decode($v);
            },
        ]
    )
);
