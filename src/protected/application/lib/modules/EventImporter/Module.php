<?php

namespace EventImporter;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Definitions;

class Module extends \MapasCulturais\Module
{

    function __construct($config = []) 
    {
        $app = App::i();

        $config += [
            "enabled" => function() use ($app){
                return $app->user->is("admin");
            },
            'frequence_list_allowed' => [
                i::__('uma vez') => 'once',
                i::__('semanal') => 'weekly',
                i::__('diariamente') => 'daily',
                'once' => 'once',
                'weekly' => 'weekly',
                'daily' => 'daily',
            ],
            'rating_list_allowed' => [
                i::__('livre'), 
                i::__('10 anos'), 
                i::__('12 anos'), 
                i::__('14 anos'), 
                i::__('16 anos'), 
                i::__('18 anos')
            ],
            'days_list_positive' => [
                i::__('sim'), 
                i::__('x'), 
                i::__('1')
            ],
            'week_days' => [
                'MODAY' => i::__('seg'),
                'TUESDAY' => i::__('ter'),
                'WEDNESDAY' => i::__('qua'),
                'THURSDAY' => i::__('qui'),
                'FRIDAY' => i::__('sex'),
                'SATURDAY' => i::__('sab'),
                'SUNDAY' => i::__('dom')
            ],
            'use_endsat' => [i::__('uma vez'), 'once'],
            'dic_months' => [
                "January" => i::__("Janeiro"),
                "February" => i::__("Fevereiro"),
                "March" => i::__("Março"),
                "April" => i::__("Abril"),
                "May" => i::__("Maio"),
                "June" => i::__("Junho"),
                "July" => i::__("Julho"),
                "August" => i::__("Agosto"),
                "September" => i::__("Setembro"),
                "October" => i::__("Outubro"),
                "November" => i::__("Novembro"),
                "December" => i::__("Dezembro"),
            ],
            'files_grp_import' => [
                'AVATAR' => 'avatar', 
                'HEADER' => 'header',
                'GALLERY' => 'gallery',
            ],
            'metalists_import' => [
                'DOWNLOADS',
                'VIDEOS',
                'LINKS'
            ],
        ];

        parent::__construct($config);

    }

    function _init()
    {
        $app = App::i();

        $app->view->enqueueStyle('app','assets-file','css/eventimporter.css');
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

        $app->registerController('eventimporter', Controller::class);

        $this->registerAgentMetadata('event_importer_processed_file', [
            'label' => 'Arquivo de processamento de importação',
            'type' => 'json',
            'private' => true,
            'default_value' => '{}'
        ]);
        
        $app->registerFileGroup(
            'agent',
            new Definitions\FileGroup(
                'event-import-file',
                ['text/csv','application/zip'],
                'O arquivo não e valido'
            )
        );
    }
}
