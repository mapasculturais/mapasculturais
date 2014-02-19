<?php
return array(
    1 => array(
        'slug' => 'tag',
        'entities' => array(
            'MapasCulturais\Entities\Space',
            'MapasCulturais\Entities\Agent',
            'MapasCulturais\Entities\Event',
            'MapasCulturais\Entities\Project'
        )
    ),

    2 => array(
        'slug' => 'area',
        'required' => "Você deve informar ao menos uma área de atuação",
        'entities' => array(
            'MapasCulturais\Entities\Space',
            'MapasCulturais\Entities\Agent'
        ),
        'restricted_terms' => array(
            'Antropologia',
            'Arqueologia',
            'Arquitetura-Urbanismo',
            'Arquivo',
            'Arte Digital',
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
            'Mídias Sociais',
            'Moda',
            'Museu',
            'Música',
            'Novas Mídias',
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

    3 => array(
        'slug' => 'linguagem',
        'entities' => array(
            'MapasCulturais\Entities\Project',
            'MapasCulturais\Entities\Event'
        ),

        'restricted_terms' => array(
            'Artes Circenses',
            'Artes Integradas',
            'Artes Visuais',
            'Audiovisual',
            'Cultura Digital',
            'Cultura Indígena',
            'Cultura Tradicional',
            'Dança',
            'Hip Hop',
            'Livre e Literatura',
            'Música',
            'Rádio',
            'Teatro',
            'Outros'
        )
    )
);