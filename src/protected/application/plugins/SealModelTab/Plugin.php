<?php

namespace SealModelTab;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Plugin extends \MapasCulturais\Plugin {

    public function _init() {
        $app = App::i();

        $app->hook('template(seal.<<create|edit>>.tabs-content):end', function() use($app){
            $opt = [
                'entity' => $this->requestedEntity,
            ];
            if (isset($app->config['seal.models']))
                $opt['model_count'] = count($app->config['seal.models']);
            else
                $opt['model_count'] = 0;
            $app->view->part('seal-model--content', $opt);
        });

        $app->hook('template(seal.<<create|edit>>.tabs):end', function() use($app){
            $this->part('seal-model--tab');
        });
    }

    public function register() {
        $app = App::i();


        $def__seal_model = new Definitions\Metadata('sealModel', ['label' => $app->txt('Model template')]);

        $app->registerMetadata($def__seal_model, 'MapasCulturais\Entities\Seal');
    }
}


abstract class SealModelTemplatePlugin extends \MapasCulturais\Plugin{
    public function _init() { }

    public function register(){
        $app = App::i();
        $app->sealModels = '12';
        $app->sealModels[] = '123';
    }

    // return label and name
    // ['label'=> 'My Label Name', 'name' => 'my_model_name']
    function getModelName(){}
}
