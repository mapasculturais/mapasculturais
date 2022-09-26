<?php

namespace EventImporter;

use MapasCulturais\App;
class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();

       //Inseri parte para upload na sidbar direita
        $app->hook('template(opportunity.edit.sidebar-right):end',function(){
           /** @var Theme $this */
           $entity = $this->controller->requestedEntity; 
           $this->part('upload-csv-event',['entity' => $entity]);
        });
    }

    function register()
    {
        $app = App::i();

        //Registro do controloador
        $app->registerController('eventimporter', Controller::class);
    }
}
