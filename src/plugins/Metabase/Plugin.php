<?php

namespace Metabase;

use MapasCulturais\App;

class Plugin extends \MapasCulturais\Plugin
{
    function __construct($config = [])
    {
        parent::__construct($config);
    }

    public function _init()
    {
        $app = App::i();
        //load css
        $app->hook('<<GET|POST>>(<<*>>.<<*>>)', function() use ($app) {
            $app->view->enqueueStyle('app-v2', 'metabase', 'css/plugin-metabase.css');
        });
        $app->hook('component(home-feature):after', function() {
            /** @var \MapasCulturais\Theme $this */
            $this->part('home-metabase');
        });

        $app->hook('template(search.agents.search-tabs):after', function(){
            $this->part('search-tabs/agent');
        });

        // $app->hook('template(search.spaces.search-tabs):after', function(){
        //     $this->part('search-tabs/space');
        // });

        $app->hook('template(search.agents.search-header):after', function(){
            $this->part('search-tabs/entity-agent-cards');
        });

        // $app->hook('template(search.spaces.search-header):after', function(){
        //     $this->part('search-tabs/entity-space-cards');
        // });

        $self= $this;
        $app->hook('app.init:after', function() use ($self){
            $this->view->metabasePlugin = $self;
        });

        $app->hook('component(mc-icon).iconset', function(&$iconset) {
            $iconset['indicator'] = 'cil:chart-line';
        });

    }

    public function register()
    {
        $app = App::i();

        $app->registerController('metabase', 'Metabase\Controllers\Metabase');
    }
}
