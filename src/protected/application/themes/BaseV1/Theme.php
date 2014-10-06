<?php
namespace MapasCulturais\Themes\BaseV1;

use MapasCulturais;
use MapasCulturais\App;


class Theme extends MapasCulturais\Theme{

    static function getThemeFolder() {
        return __DIR__;
    }

    function head() {
        parent::head();

        $app = App::i();

        $app->printStyles('vendor');
        $app->printStyles('fonts');
        $app->printStyles('app');
        $app->applyHook('mapasculturais.styles');

        $app->printScripts('vendor');
        $app->printScripts('app');
        $app->applyHook('mapasculturais.scripts');
    }
}