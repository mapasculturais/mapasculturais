# plugin-Metabase

Plugin que faz o embed dos dashboards do metabase no MapasCulturais

## Instalação e Configuração

Faça download ou um clone do plugin na pasta `src/plugins` do Mapas Culturais.

Para clonar o plugin, utilize o comando:
```
git clone https://github.com/mapasculturais/plugin-Metabase.git Metabase
```

No arquivo de configuração do Mapas Culturais, config.php, você deve:

1. Ativar o plugin
2. Criar arquivo de configuração do plugin

Para ativar o plugin, adicione na sua array de Plugins:
```
'plugins' => [
    // ... outros plugins
    'Metabase',
],
```

Criação do arquivo de configuração do plugin Metabase.php no diretório config/ com seguinte conteúdo:

```
'[
    'Metabase' => [
        'config' => [
            'links' => [
                'NOME_DASHBOARD' => [
                    'link' => 'LINK_DASHBOARD_DO_METABASE_PUBLICO',
                    'text' => 'DESCRICAO_DASHBOARD.',
                    'title' => 'TITULO_DASHBOARD'
                ],
            ],
            'cards' => [
              // cards que aparecem na home
              'home' => [
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
              ],
              // cards que aparecem na entidade (página de pesquisa)
              'entities' => [
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
              ]
            ]
        ]
    ]
];
```