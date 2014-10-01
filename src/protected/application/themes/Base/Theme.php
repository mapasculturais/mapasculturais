<?php
namespace MapasCulturais\Themes\Base;
use MapasCulturais\App;
use MapasCulturais\Traits;

abstract class Theme{
    use Traits\MagicGetter,
        Traits\MagicSetter,
        Traits\Singleton;

    protected $jsObject = array();

    public function __construct() {
        $app = App::i();
        $this->jsObject['baseURL'] = $app->baseUrl;
        $this->jsObject['assetURL'] = $app->assetUrl;
    }

    function isEditable(){
        return (bool) preg_match('#^\w+/(create|edit)$#', App::i()->view->template);
    }

    function bodyBegin(){
        App::i()->applyHook('mapasculturais.body:before');
    }

    function bodyEnd(){
        App::i()->applyHook('mapasculturais.body:after');
    }

    function bodyProperties(){
        $app = App::i();

        $body_properties = array();

        foreach ($app->view->bodyProperties as $key => $val)
            $body_properties[] = "{$key}=\"$val\"";

        $body_properties[] = 'class="' . implode(' ', $app->view->bodyClasses->getArrayCopy()) . '"';

        echo implode(' ', $body_properties);;
    }

    function head(){
        $app = App::i();

        $app->applyHook('mapasculturais.head');

        $app->printStyles('vendor');
        $app->printStyles('fonts');
        $app->printStyles('app');
        $app->printScripts('vendor');
        $app->printScripts('app');

        $app->applyHook('mapasculturais.scripts');
    }

    /**
     * @return \MapasCulturais\Themes\Base\AssetManager Asset Manager
     */
    abstract function getAssetManager();
}