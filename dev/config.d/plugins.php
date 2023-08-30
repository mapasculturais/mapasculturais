<?php

return [
    'plugins' => [
        'MultipleLocalAuth' => [
            'namespace' => 'MultipleLocalAuth',
        ],
        'Metabase' => [
            'namespace' => 'Metabase',
            'config' => [
                'links' => [
                    'opportunities' => [
                        'title' => 'Painel sobre oportunidades',
                        'link' => 'https://bi.mapacultural.pe.gov.br/public/dashboard/39ce65ee-9d2b-432e-b9d2-e688b18ece7d',
                        'text' => 'Tenha acesso ao número de oportunidades e  editais cadastrados, a quantidade de pessoas participantes inscritas, o perfil demográfico e mais informações.',
                    ],
                    'users' => [
                        'title' => 'Painel sobre usuários',
                        'link' => 'http://bi.mapacultural.pe.gov.br/public/dashboard/44f04f96-70aa-4fb6-bcc6-6ec06c72d8e8',
                        'text' => 'Acesse e confira os dados gerais dos usuários da plataforma, como o total de pessoas cadastradas, atividades dos usuários e outras informações. ',

                    ],
                    'entities' => [
                        'title' => 'Painel geral das entidades ',
                        'link' => 'https://bi.mapacultural.pe.gov.br/public/dashboard/b0d48d8e-d5c2-4a7b-a56f-207c0caa77bc',
                        'text' => 'Confira dados relacionados às entidades cadastradas na plataforma, como agentes individuais e coletivos, oportunidades, espaços, eventos e projetos.',
                    ],
                    'agent' => [
                        'title' => 'Painel sobre agentes individuais',
                        'link' => 'https://bi.mapacultural.pe.gov.br/public/dashboard/dbf9eb35-9304-49a5-9c63-646687bdde41',
                        'text' => 'Saiba os números de agentes individuais cadastrados, quantos são criados mensalmente, por onde estão distribuídos no território e outras informações.',
                    ],
                    'agents' => [
                        'title' => 'Painel sobre agentes coletivos',
                        'link' => 'https://bi.mapacultural.pe.gov.br/public/dashboard/3b01b14a-d1e4-4e42-bb83-220352704e26',
                        'text' => 'Dados sobre a quantidade de  coletivos e instituições (com ou sem CNPJ) cadastrados, por onde se distribuem pelo estado e outras informações.',
                    ],

                    'spaces' => [
                        'title' => 'Painel sobre espaços',
                        'link' => 'https://bi.mapacultural.pe.gov.br/public/dashboard/7eb10b1d-43f3-4adf-aabc-fa46bdd0073a',
                        'text' => 'Conheça, entre outras informações, por onde os espaços estão distribuídos, a quantidade de espaços cadastros na plataforma, os tipos e as áreas de atuação.',
                    ],
                    'events' => [
                        'title' => 'Painel sobre eventos',
                        'link' => 'https://bi.mapacultural.pe.gov.br/public/dashboard/1bfdba17-1340-4ca9-9bc6-ab7dde8c8503',
                        'text' => 'Indicadores relacionados a quantidade de eventos cadastrados, às linguagens culturais e características, as datas de criação e também eventos agendados. ',
                    ],
                    'projetos' => [
                        'title' => 'Painel sobre projetos',
                        'link' => 'https://bi.mapacultural.pe.gov.br/public/dashboard/3107052a-bdda-4113-b635-6d7e4a1df10b',
                        'text' => 'Tenha acesso ao número total de projetos cadastrados, projetos certificados, quantidade de projetos com subprojetos, os tipos e outros dados. ',
                    ],
                ]
            ],
        ]
    ]
];
