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

        $app->hook( 'template(seal.<<create|edit>>.tabs-content):end', function() use($app){
            $entity = $app->view->controller->requestedEntity;
            $app->view->part( 'seal-model--content',['entity' => $entity] );
        });

        $app->hook( 'template(seal.<<create|edit>>.tabs):end', function() use($app){
            $this->part( 'seal-model--tab' );
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

            $entity = $relation->seal;
            $period = new \DateInterval("P" . $entity->validPeriod . "M");
            $dateIni = $relation->createTimestamp->format("d/m/Y");
            $dateFin = $relation->createTimestamp->add($period)->format("d/m/Y");

            $replaces = [
                "\t"                        =>"&nbsp;&nbsp;&nbsp;&nbsp",
                "[sealName]"                => $relation->seal->name,
                "[sealOwner]"               => $relation->seal->agent->name,
                "[sealShortDescription]"    => $relation->seal->shortDescription,
                "[sealRelationLink]"        => $app->createUrl('seal','printsealrelation',[$relation->id]),
                "[entityDefinition]"        => $relation->owner->entityType,
                "[entityName]"              => '<span class="entity-name">'.$relation->owner->name.'</span>',
                "[dateIni]"                 => $dateIni,
                "[dateFin]"                 => $dateFin
            ];

            $msg = $relation->seal->certificateText;
            foreach ($replaces as $k => $v)
                $msg = str_replace($k, $v, $msg);

            include PLUGINS_PATH.$relation->seal->seal_model.'/printsealrelation.php';

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

        $that = $this;

        $app->hook('GET(seal.printsealrelation):before', function() use($app, $that){
            $id = $this->data['id'];
            $relation = $app->repo('SealRelation')->find($id);
            if ($relation->seal->seal_model == $that->getModelName()['name']){
                $app->view->assetManager->publishAsset('img/'.$that->getBackgroundImage());
                $app->view->enqueueStyle('app', $that->getModelName()['name'], 'css/'.$that->getCssFileName());
            }
        });
    }


    public function register(){}

    // return label and name
    // ['label'=> 'My Label Name', 'name' => 'my_model_name']
    function getModelName(){}

    // return a unique CSS file name for the Model
    function getCssFileName(){}

    // return a unique Image File Name for the MOdel background
    function getBackgroundImage(){}

}
