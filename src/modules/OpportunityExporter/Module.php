<?php
namespace OpportunityExporter;

use MapasCulturais\Module as MapasCulturaisModule;
use MapasCulturais\App;

class Module extends MapasCulturaisModule {
    public function _init() { }

    public function register() { 
        $app = App::i();
        $app->registerController('opportunity-exporter', Controller::class);
    }

}