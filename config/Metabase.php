<?php

return [
    'Metabase' => [
        'enabled' => env('METABASE_ENABLED', false),
        'config' => [
            'links' => [
                'painel-agentes' => [
                    'link' => 'https://metabase.cultura.gov.br/public/dashboard/5f06b042-190b-4f3e-8652-26af9283a562', // dashboard dos agentes
                    'text' => 'Agentes Culturais',
                    'title' => 'Agentes Culturais',
                    'entity' => 'Agent'
                ],
                'painel-espacos' => [
                    'link' => 'https://mapa.cultura.gov.br/', // dashboard dos espaços
                    'text' => 'Saiba os números de espaços cadastrados, quantos são criados mensalmente, por onde estão distribuídos no território e outras informações.',
                    'title' => 'Painel sobre espaços',
                    'entity' => 'Space'
                ],
                'painel-oportunidades' => [
                    'link' => 'https://metabase.cultura.gov.br/public/dashboard/21db967a-ee98-4e82-9a18-fc6a41a1f1da', //dashboard das oportunidade
                    'text' => 'Ações Culturais e Instrumentos de Fomento',
                    'title' => 'Ações Culturais e Instrumentos de Fomento',
                    'entity' => 'Opportunity'
                ],
            ],
            'cards' => [
                    'home' => [
                        [
                            'type' => 'space',
                            'label' => '',
                            'icon'=> 'space',
                            'iconClass'=> 'space__color',
                            'panelLink'=> 'painel-espacos',
                            'data'=> [
                                [
                                    'icon'=> 'space',
                                    'label' => 'Espaços cadastrados',
                                    'entity' => 'MapasCulturais\\Entities\\Space',
                                    'query' => [],
                                    'value' => null
                                ],
                            ]
                        ],
                        [
                            'type' => 'space',
                            'label' => '',
                            'icon'=> 'space',
                            'iconClass'=> 'space__color',
                            'panelLink'=> 'painel-espacos',
                            'data'=> [
                                [
                                    'icon'=> 'space',
                                    'label'=> 'Espaços certificados',
                                    'entity'=> 'MapasCulturais\\Entities\\Space',
                                    'query'=> [
                                        '@verified'=> 1
                                    ],
                                    'value'=> null
                                ]
                            ]
                        ],
                        [
                            'type' => 'agent',
                            'label' => '',
                            'icon'=> 'agent',
                            'iconClass'=> 'agent__color',
                            'panelLink'=> 'painel-agentes',
                            'data'=> [
                                [
                                    'icon'=> 'agent',
                                    'label' => 'Agentes cadastrados',
                                    'entity' => 'MapasCulturais\\Entities\\Agent',
                                    'query' => [],
                                    'value' => null
                                ],
                            ]
                        ],
                        [
                            'type' => 'agent',
                            'label' => '',
                            'icon'=> 'agent',
                            'iconClass'=> 'agent__color',
                            'panelLink'=> 'painel-agentes',
                            'data'=> [
                                [
                                    'icon'=> 'agent',
                                    'label' => 'Agentes individuais',
                                    'entity' => 'MapasCulturais\\Entities\\Agent',
                                    'query' => ['type' => 'EQ(1)'],
                                    'value' => null
                                ],
                            ]
                        ], 
                        [
                            'type' => 'agent',
                            'label' => '',
                            'icon'=> 'agent',
                            'iconClass'=> 'agent__color',
                            'panelLink'=> 'painel-agentes',
                            'data'=> [
                                [
                                    'icon'=> 'agent',
                                    'label' => 'Agentes coletivos',
                                    'entity' => 'MapasCulturais\\Entities\\Agent',
                                    'query' => ['type' => 'EQ(2)'],
                                    'value' => null
                                ],
                            ]
                        ],
                        // opportunity
                        [
                            'type' => 'opportunity',
                            'label' => 'Oportunidades',
                            'icon'=> 'opportunity',
                            'iconClass'=> 'opportunity__color',
                            'panelLink'=> 'painel-oportunidades',
                            'data'=> [
                                [
                                    'label' => 'Oportunidades criadas',
                                    'entity' => 'MapasCulturais\\Entities\\Opportunity',
                                    'query' => [],
                                    'value' => null
                                ],
                                [
                                    'label' => 'Oportunidades certificadas',
                                    'entity' => 'MapasCulturais\\Entities\\Opportunity',
                                    'query'=> [
                                        '@verified'=> 1
                                    ],
                                    'value' => null
                                ],
                            ]
                        ]
                            
                    ],
                    'entities' => [
                        [
                            'type' => 'space',
                            'label' => '',
                            'icon'=> 'space',
                            'iconClass'=> 'space__color',
                            'panelLink'=> 'painel-espacos',
                            'data'=> [
                                [
                                    'id' => 'espacos-cadastrados',
                                    'icon'=> 'space',
                                    'label' => 'Espaços cadastrados',
                                    'entity' => 'MapasCulturais\\Entities\\Space',
                                    'query' => [],
                                    'value' => null
                                ],
                            ]
                        ],
                        [
                            'type' => 'space',
                            'label' => '',
                            'icon'=> 'space',
                            'iconClass'=> 'space__color',
                            'panelLink'=> 'painel-espacos',
                            'data'=> [
                                [
                                    'id' => 'espacos-certificados',
                                    'icon'=> 'space',
                                    'label'=> 'Espaços certificados',
                                    'entity'=> 'MapasCulturais\\Entities\\Space',
                                    'query'=> [
                                        '@verified'=> 1
                                    ],
                                    'value'=> null
                                ]
                            ]
                        ],
                        [
                            'type' => 'agent',
                            'label' => '',
                            'icon'=> 'agent',
                            'iconClass'=> 'agent__color',
                            'panelLink'=> 'painel-agentes',
                            'data'=> [
                                [
                                    'id' => 'agentes-cadastrados',
                                    'icon'=> 'agent',
                                    'label' => 'Agentes cadastrados',
                                    'entity' => 'MapasCulturais\\Entities\\Agent',
                                    'query' => [],
                                    'value' => null
                                ],
                            ]
                        ],
                        [
                            'type' => 'agent',
                            'label' => '',
                            'icon'=> 'agent',
                            'iconClass'=> 'agent__color',
                            'panelLink'=> 'painel-agentes',
                            'data'=> [
                                [
                                    'id' => 'agentes-individuais',
                                    'icon'=> 'agent',
                                    'label' => 'Agentes individuais',
                                    'entity' => 'MapasCulturais\\Entities\\Agent',
                                    'query' => ['type' => 'EQ(1)'],
                                    'value' => null
                                ],
                            ]
                        ], 
                        [
                            'type' => 'agent',
                            'label' => '',
                            'icon'=> 'agent',
                            'iconClass'=> 'agent__color',
                            'panelLink'=> 'painel-agentes',
                            'data'=> [
                                [
                                    'id' => 'agentes-coletivos',
                                    'icon'=> 'agent',
                                    'label' => 'Agentes coletivos',
                                    'entity' => 'MapasCulturais\\Entities\\Agent',
                                    'query' => ['type' => 'EQ(2)'],
                                    'value' => null
                                ],
                            ]
                        ],
                        [
                            'type' => 'agent',
                            'label' => '',
                            'icon'=> 'agent',
                            'iconClass'=> 'agent__color',
                            'panelLink'=> 'painel-agentes',
                            'data'=> [
                                [
                                    'id' => 'agentes-cadastrados-7-dias',
                                    'icon'=> 'agent',
                                    'label' => 'Cadastrados nos últimos 7 dias',
                                    'entity' => 'MapasCulturais\\Entities\\Agent',
                                    'query'=> [
                                        '@select' => 'createTimestamp'
                                    ],
                                    'value' => null
                                ],
                            ]
                        ],
                        [
                            'type' => 'opportunity',
                            'label' => 'Oportunidades',
                            'icon'=> 'opportunity',
                            'iconClass'=> 'opportunity__color',
                            'panelLink'=> 'painel-oportunidades',
                            'data'=> [
                                [
                                    'icon'=> 'opportunity',
                                    'label' => 'Oportunidades criadas',
                                    'entity' => 'MapasCulturais\\Entities\\Opportunity',
                                    'query' => [],
                                    'value' => null
                                ],
                            ]
                        ],
                        [
                            'type' => 'opportunity',
                            'label' => 'Oportunidades certificadas',
                            'icon'=> 'opportunity',
                            'iconClass'=> 'opportunity__color',
                            'panelLink'=> 'painel-oportunidades',
                            'data'=> [
                                [
                                    'icon'=> 'opportunity',
                                    'label' => 'Oportunidades certificadas',
                                    'entity' => 'MapasCulturais\\Entities\\Opportunity',
                                    'query'=> [
                                        '@verified'=> 1
                                    ],
                                    'value' => null
                                ],
                            ]
                        ]
                    ]
            ]
        ]
    ]
];
