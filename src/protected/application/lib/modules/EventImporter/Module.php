<?php

namespace EventImporter;

use MapasCulturais\App;
class Module extends \MapasCulturais\Module
{
    function _init()
    {
        $app = App::i();

        //Inseri parte para upload na sidbar direita
        $app->hook('template(panel.events.settings-nav):begin', function() use($app) {
            /** @var Theme $this */
            $this->controller = $app->controller('agent');
            $this->part('upload-csv-event',['entity' => $app->user->profile]);
            $this->controller = $app->controller('panel');

        });
    }

    function register()
    {
        $app = App::i();

        //Registro do controloador
        $app->registerController('eventimporter', Controller::class);

        $app->registerFileGroup(
            'agent',
            new Definitions\FileGroup(
                'event-import-file', // nome do grupo
                ['text/csv','application/zip'], // validação do mime type
                'O arquivo não e valido' // mensagem de erro quando a validação não passa
            )
        );
       
    }
}
