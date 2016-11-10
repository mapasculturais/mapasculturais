<?php

namespace SealModelTab;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Plugin extends \MapasCulturais\Plugin {

    public function _init() {

        $app = App::i();

        $app->sealModels = [];

        $app->hook( 'template(seal.<<create|edit>>.tabs-content):end', function() use($app){
            $entity = $app->view->controller->requestedEntity;
            $app->view->part( 'seal-model--content',['entity' => $entity] );
        });

        $app->hook( 'template(seal.<<create|edit>>.tabs):end', function() use($app){
            $this->part( 'seal-model--tab' );
        });

        $app->hook('GET(seal.printsealrelation):before', function(){
            $app = App::i();
            $id = $this->data['id'];
            $relation = $app->repo('SealRelation')->find($id);
            $this->requireAuthentication();
            include PLUGINS_PATH.$relation->seal->seal_model.'/printsealrelation.php';
            exit();
        });

    }

    public function register() {
        $app = App::i();

        $conf = [
            'label'     => $app->txt('Model template'),
            'type'      => 'select',
            'options'   => []
        ];

        foreach ($app->sealModels as $v)
            $conf['options'][$v['name']] = $v['label'];

        $def__seal_model = new Definitions\Metadata('seal_model', $conf);

        $app->registerMetadata( $def__seal_model, 'MapasCulturais\Entities\Seal' );
    }
}


abstract class SealModelTemplatePlugin extends \MapasCulturais\Plugin{
    public function _init() {
        $app = App::i();
        $app->sealModels[] = $this->getModelName();
    }


    public function register(){}

    // return label and name
    // ['label'=> 'My Label Name', 'name' => 'my_model_name']
    function getModelName(){}

}
