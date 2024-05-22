<?php

namespace Metabase;

use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin
{
    function __construct($config = [])
    {
        $config += [
            'links' => [],
            'cards' => [
                [
                    "label" => "Espaços",
                    "icon"=> "space",
                    "iconClass"=> "space__color",
                    "panelLink"=> "painel-espacos",
                    "data"=> [
                        [
                            "label" => "espaços cadastrados",
                            "entity" => "MApasCulturais\\Entities\\Space",
                            "query" => [],
                            "value" => 2140
                        ],
                        [
                            "label"=> "espaços certificados",
                            "entity"=> "MApasCulturais\\Entities\\Space",
                            "query"=> [
                                "@verified"=> 1
                            ],
                            "value"=> 0
                        ]
                    ]
                ],
                [
                    "label" => "Agentes",
                    "icon"=> "agent-1",
                    "iconClass"=> "agent__color",
                    "panelLink"=> "painel-agentes",
                    "data"=> [
                        [
                            "label" => "agentes individuais cadastrados",
                            "entity" => "MApasCulturais\\Entities\\Agent",
                            "query" => [],
                            "value" => 2140
                        ],
                    ]
                ]
            ],
        ];

        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        //load css
        $app->hook('<<GET|POST>>(<<metabase|site>>.<<*>>)', function() use ($app) {
            $app->view->enqueueStyle('app-v2', 'metabase', 'css/plugin-metabase.css');
        });
        $app->hook("component(home-feature):after", function() {
            /** @var \MapasCulturais\Theme $this */
            $this->part('home-metabase');
        });

        $self= $this;
        $app->hook("app.init:after", function() use ($self){
            $this->view->metabasePlugin = $self;
        });

    }

    public function register()
    {
        $app = App::i();

        $app->registerController('metabase', 'Metabase\Controllers\Metabase');
    }
}
