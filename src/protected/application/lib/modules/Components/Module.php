<?php
namespace Components;

use MapasCulturais\App;

class Module extends \MapasCulturais\Module {
    function _init()
    {
        $app = App::i();

        $app->view->enqueueScript('vendor', 'vue3', 'https://unpkg.com/vue@3');
        $app->view->enqueueScript('vendor', 'vue-demi', 'https://unpkg.com/vue-demi');
        $app->view->enqueueScript('vendor', 'pinia', 'https://unpkg.com/pinia', ['vue3', 'vue-demi']);
        
        $app->view->enqueueScript('app', 'components-api', 'js/components-base/API.js');
        $app->view->enqueueScript('app', 'components-entity', 'js/components-base/Entity.js', ['components-api']);

        if (isset($this->jsObject['componentTemplates'])) {
            $this->jsObject['componentTemplates'] = [];
        }

        $app->hook('GET(panel.rbal)', function(){
            $this->render('rbal');
        });

    }

    function register()
    {
        
    }
}