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
                'description' => i::__('Título do header da home'),
            ],
            [
                'slug' => 'home-header.description',
                'description' => i::__('Descrição do header da home'),
            ],
        ],
        'image' => [
            'description' => i::__('Imagem do header da home'),
            'group' => 'header',
            'aspectRatio' => 1500/600,
        ]
    ],
    [
        'sectionName' => i::__('Titulo da oportunidade da home'),
        'texts' => [
            [
                'slug' => 'home-opportunities.title',
                'description' => i::__('Título da oportunidade da home'),
            ],
            [
                'slug' => 'home-opportunities.description',
                'description' => i::__('Descrição da oportunidade da home'),
            ],
        ],
    ],
    [
        'sectionName' => i::__('Entidades'),
        'texts' => [
            [
                'slug' => 'home-entities.title',
                'description' => i::__('Título da seção entidade da home'),
            ],
            [
                'slug' => 'home-entities.description',
                'description' => i::__('Descrição da seção entidade da home'),
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
        'image' => [
            'description' => i::__('Imagem de fundo da oportunidade'),
            'group' => 'opportunityBanner',
            'aspectRatio' => 800/300,
        ]
    ],
    [
        'sectionName' => i::__('Eventos'),
        'texts' => [
            [
                'slug' => 'home-entities.events',
                'description' => i::__('Título dos eventos da home')
            ],
        ],
        'image' => [
            'description' => i::__('Imagem de fundo do evento'),
            'group' => 'eventBanner',
            'aspectRatio' => 800/300,
        ]
    ],
    [
        'sectionName' => i::__('Espaços'),
        'texts' => [
            [
                'slug' => 'home-entities.spaces',
                'description' => i::__('Título dos espaços da home')
            ],
        ],
        'image' => [
            'description' => i::__('Imagem de fundo do espaço'),
            'group' => 'spaceBanner',
            'aspectRatio' => 800/300,
        ]
    ],
    [
        'sectionName' => i::__('Agentes'),
        'texts' => [
            [
                'slug' => 'home-entities.agents',
                'description' => i::__('Título dos agentes da home')
            ],
        ],
        'image' => [
            'description' => i::__('Imagem de fundo do agente'),
            'group' => 'agentBanner',
            'aspectRatio' => 800/300,
        ]
    ],
    [
        'sectionName' => i::__('Projetos'),
        'texts' => [
            [
                'slug' => 'home-entities.projects',
                'description' => i::__('Título dos projetos da home')
            ],
        ],
        'image' => [
            'description' => i::__('Imagem de fundo do projeto'),
            'group' => 'projectBanner',
            'aspectRatio' => 800/300,
        ]
    ],
    [
        'sectionName' => i::__('Descrição de registro da home'),
        'texts' => [
            [
                'slug' => 'home-feature.title',
                'description' => i::__('Título de registro da home')
            ],
            [
                'slug' => 'home-feature.description',
                'description' => i::__('Descrição de registro da home')
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
        'image' => [
            'description' => i::__('Imagem de fundo da seção de fazer cadastro'),
            'group' => 'signupBanner',
            'aspectRatio' => 1920/386,
        ]
    ],
    [
        'sectionName' => i::__('Visualize também no mapa'),
        'texts' => [
            [
                'slug' => 'home-map.title',
                'description' => i::__('Título da visualização do mapa da home')
            ],
            [
                'slug' => 'home-map.description',
                'description' => i::__('Descrição da visualização do mapa da home')
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
];

$app->applyHook('home-customizer', [&$texts]);

$this->jsObject['config']['homeCustomizer'] = $texts;
