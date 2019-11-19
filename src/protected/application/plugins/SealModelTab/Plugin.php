<?php

namespace SealModelTab;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;

class Plugin extends \MapasCulturais\Plugin {

    public function _init() {

        $app = App::i();

        $that = $this;

        $app->sealModels = [];

        $app->hook('template(seal.sealrelation.print-certificate):after', function($relation) use($app){
            
            if($app->isEnabled('seals') && 
                $relation->seal->seal_model &&
                !$app->user->is('guest') &&
                (   $app->user->is('superAdmin') || 
                    $app->user->is('admin') || 
                    $app->user->profile->id == $relation->agent->id
                )
            ) {
                
                $this->part('seal-model--printCertificate', ['relation' => $relation]);
            }
        });

        $app->hook( 'template(seal.<<create|edit>>.tabs-content):end', function() use($app){
            $view = $app->view->enqueueScript('app', 'seal_model_tab', 'js/seal-model-preview.js');
            $entity = $app->view->controller->requestedEntity;
            $app->view->part( 'seal-model--content',['entity' => $entity] );
        });

        $app->hook( 'template(seal.<<create|edit>>.tabs):end', function() use($app){
            $this->part( 'seal-model--tab' );
        });


        $app->hook('GET(seal.sealModelPreview)', function() use($app){
            $preview_name = $app->request->get('p');
            $preview_url = '';
            foreach ($app->sealModels as $v){
                if ($v['name'] == $preview_name){
                    $preview_url = isset($v['preview']) ? $v['preview'] : '';
                    break;
                }
            }
            if ($preview_url)
                $app->view->asset('img/'.$preview_url);
            else
                echo '';
        });

        $app->hook('GET(seal.printsealrelation):before', function() use($app, $that){
            $id = $this->data['id'];
            $relation = $app->repo('SealRelation')->find($id);

            if (!$relation->seal->seal_model)
                return;

            $view = $app->view;
            $view->enqueueStyle('app', 'seal_model_tab', 'css/seal-model-tab.css');


            $this->requireAuthentication();
            $this->layout = 'nolayout';

            
            $msg = $relation->getCertificateText();
            
            $msg = htmlspecialchars_decode(htmlentities($msg), ENT_NOQUOTES);

            include PLUGINS_PATH.$relation->seal->seal_model.'/printsealrelation.php';

        });

    }

    public function register() {
        $app = App::i();

        $conf = [
            'label'     => \MapasCulturais\i::__('Modelo de selo'),
            'type'      => 'select',
            'options'   => [
                '' => \MapasCulturais\i::__('Nenhum modelo Selecionado')
            ]
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
        $app->sealModels[] = $this->getModelData();

        $data = $this->getModelData();

        $app->hook('GET(seal.printsealrelation):before', function() use($app, $data){
            $id = $this->data['id'];
            $relation = $app->repo('SealRelation')->find($id);
            if ($relation->seal->seal_model == $data['name']){
                $app->view->assetManager->publishAsset('img/' . $data['background']);
                $app->view->enqueueStyle('app', $data['name'], 'css/' . $data['css']);
            }
        });
    }

    public function register(){}

    // ['label'=> 'My Label Name', 'name' => 'my_model_name']
    function getModelData(){}

    // return a unique Image File Name for the MOdel background
    // function getBackgroundImage(){}

}
