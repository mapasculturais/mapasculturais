<?php

/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\App;
use MapasCulturais\i;

$app = App::i();

$texts = [
    [
        'sectionName' => i::__('Header da home'),
        'texts' => [
            [
                'slug' => 'home-header.title',
                'description' => i::__('Título do header da home')
            ],
            [
                'slug' => 'home-header.description',
                'description' => i::__('Descrição do header da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Faça seu cadastro na home'),
        'texts' => [
            [
                'slug' => 'home-register.title',
                'description' => i::__('Título da seção faça seu cadastro da home')
            ],
            [
                'slug' => 'home-register.description',
                'description' => i::__('Descrição da seção faça seu cadastro da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Desenvolvedores'),
        'texts' => [
            [
                'slug' => 'home-developers.title',
                'description' => i::__('Título da seção desenvolvedores da home')
            ],
            [
                'slug' => 'home-developers.description',
                'description' => i::__('Descrição da seção desenvolvedores da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Entidades'),
        'texts' => [
            [
                'slug' => 'home-entities.title',
                'description' => i::__('Título da seção entidade da home')
            ],
            [
                'slug' => 'home-entities.description',
                'description' => i::__('Descrição da seção entidade da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Oportunidades'),
        'texts' => [
            [
                'slug' => 'home-entities.opportunities',
                'description' => i::__('Título das oportunidade da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Eventos'),
        'texts' => [
            [
                'slug' => 'home-entities.events',
                'description' => i::__('Título dos eventos da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Espaços'),
        'texts' => [
            [
                'slug' => 'home-entities.spaces',
                'description' => i::__('Título dos espaços da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Agentes'),
        'texts' => [
            [
                'slug' => 'home-entities.agents',
                'description' => i::__('Título dos agentes da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Projetos'),
        'texts' => [
            [
                'slug' => 'home-entities.projects',
                'description' => i::__('Título dos projetos da home')
            ],
        ],
    ],
    [
        'sectionName' =>  i::__('Registro'),
        'texts' => [
            [
                'slug' => 'home-feature.title',
                'description' => i::__('Título de registro da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Descrição de registro da home'),
        'texts' => [
            [
                'slug' => 'home-feature.description',
                'description' => i::__('Descrição de registro da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Destaques'),
        'texts' => [
            [
                'slug' => 'home-feature.destaques',
                'description' => i::__('Destaque de registro da home')
            ],
        ],
    ],
    [
        'sectionName' =>  i::__('Titulo do Header'),
        'texts' => [
            [
                'slug' => 'home-header.title',
                'description' => i::__('Título do header')
            ],
            [
                'slug' => 'home-header.description',
                'description' => i::__('Descrição do header')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Titulo do Header'),
        'texts' => [
            [
                'slug' => 'home-opportunities.title',
                'description' => i::__('Título da oportunidade da home')
            ],
            [
                'slug' => 'home-opportunities.description',
                'description' => i::__('Descrição da oportunidade da home')
            ],
        ],
    ],
    [
        'sectionName' => i::__('Registro'),
        'texts' => [
            [
                'slug' => 'home-register.title',
                'description' => i::__('Título de registro da home')
            ],
        ],
    ]
];
$this->jsObject['config']['homeTexts'] = $texts;
