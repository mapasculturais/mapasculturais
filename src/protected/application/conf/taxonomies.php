<?php
use MapasCulturais\i;

return array(
    1 => array(
       // 'slug' => i::__('tag'),
    'slug' => 'tag',
        'entities' => array(
            'MapasCulturais\Entities\Space',
            'MapasCulturais\Entities\Agent',
            'MapasCulturais\Entities\Event',
            'MapasCulturais\Entities\Project',
            'MapasCulturais\Entities\Opportunity',
        )
    ),

    2 => array(
        //'slug' => i::__('area'),
'slug' => 'area',
        'required' => i::__("Você deve informar ao menos uma área de atuação"),
        'entities' => array(
            'MapasCulturais\Entities\Space',
            'MapasCulturais\Entities\Agent'
        ),
        'restricted_terms' => array(
            i::__('Artes Circenses'),
            i::__('Antropologia'),
            i::__('Arqueologia'),
            i::__('Arquitetura-Urbanismo'),
            i::__('Arquivo'),
            i::__('Arte de Rua'),
            i::__('Arte Digital'),
            i::__('Artes Visuais'),
            i::__('Artesanato'),
            i::__('Audiovisual'),
            i::__('Cinema'),
            i::__('Circo'),
            i::__('Comunicação'),
            i::__('Cultura Cigana'),
            i::__('Cultura Digital'),
            i::__('Cultura Estrangeira (imigrantes)'),
            i::__('Cultura Indígena'),
            i::__('Cultura LGBT'),
            i::__('Cultura Negra'),
            i::__('Cultura Popular'),
            i::__('Dança'),
            i::__('Design'),
            i::__('Direito Autoral'),
            i::__('Economia Criativa'),
            i::__('Educação'),
            i::__('Esporte'),
            i::__('Filosofia'),
            i::__('Fotografia'),
            i::__('Gastronomia'),
            i::__('Gestão Cultural'),
            i::__('História'),
            i::__('Jogos Eletrônicos'),
            i::__('Jornalismo'),
            i::__('Leitura'),
            i::__('Literatura'),
            i::__('Livro'),
            i::__('Meio Ambiente'),
            i::__('Mídias Sociais'),
            i::__('Moda'),
            i::__('Museu'),
            i::__('Música'),
            i::__('Novas Mídias'),
            i::__('Ópera'),
            i::__('Patrimônio Cultural'),
            i::__('Patrimônio Imaterial'),
            i::__('Patrimônio Material'),
            i::__('Pesquisa'),
            i::__('Produção Cultural'),
            i::__('Rádio'),
            i::__('Saúde'),
            i::__('Sociologia'),
            i::__('Teatro'),
            i::__('Televisão'),
            i::__('Turismo'),
           i::__('Outros')
        )
    ),

    3 => array(
        'slug' => 'linguagem',
        'required' => i::__("Você deve informar ao menos uma linguagem"),
        'entities' => array(
            'MapasCulturais\Entities\Event'
        ),

        'restricted_terms' => array(
            i::__('Artes Circenses'),
            i::__('Artes Integradas'),
            i::__('Artes Visuais'),
            i::__('Audiovisual'),
            i::__('Cinema'),
            i::__('Cultura Digital'),
            i::__('Cultura Indígena'),
            i::__('Cultura Tradicional'),
            i::__('Curso ou Oficina'),
            i::__('Dança'),
            i::__('Exposição'),
            i::__('Hip Hop'),
            i::__('Livro e Literatura'),
            i::__('Música Popular'),
            i::__('Música Erudita'),
            i::__('Palestra, Debate ou Encontro'),
            i::__('Rádio'),
            i::__('Teatro'),
            i::__('Outros')
        )
    )
);
