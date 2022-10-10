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
            "header_default" => [
                'NAME' => [i::__('nome'),'name'],
                'SUBTITLE' => ['subtitle',i::__('subtítulo'),i::__('subtitulo')],
                'SHORT_DESCRIPTION' => ['short_description',i::__('descrição curta'),i::__('descricao curta')],
                'DESCRIPTION' => ['description',i::__('descrição longa'),i::__('descricao longa')],
                'SITE' => ['site'],
                'FACEBOOK' => ['facebook'],
                'INSTAGRAM' => ['instagram'],
                'TWITTER' => ['twitter'],
                'YOUTUBE' => ['youtube'],
                'LINKEDIN' => ['linkedin'],
                'SPOTIFY' => ['spotify'],
                'PINTEREST' => ['pinterest'],
                'TOTAL_DE_PUBLICO' => [i::__('total_de_publico')],
                'INSCRICOES' => [i::__('inscricoes'),i::__('inscrições')],
                'CLASSIFICATION' => ['classification','rating',i::__('clasificação etária'),i::__('clasificacao etaria'),i::__('faixa etária'),i::__('faixa etaria')],
                'LANGUAGE' => ['language',i::__('línguagem'),i::__('língua'),i::__('lingua'),i::__('lingua')],
                'PROJECT' => ['project',i::__('projeto')],
                'OWNER' => ['owner',i::__('proprietário'),i::__('proprietario')],
                'SPACE' => ['space',i::__('espaço')],
                'STARTS_AT' => ['starts_at',i::__('hora inicial'),i::__('hora')],
                'ENDS_AT' => ['ends_at','hora final',i::__('hora')],
                'FREQUENCY' => ['frequency',i::__('frequencia')],
                'STARTS_ON' => ['starts_on',i::__('data inicial'),i::__('data'),'date'],
                'ENDS_ON' => ['ends_on',i::__('data final'),'date'],
                'MODAY' => ['moday',i::__('segunda')],
                'TUESDAY' => ['tuesday',i::__('terça'),i::__('ter')],
                'WEDNESDAY' => ['wednesday',i::__('quarta'),i::__('qua')],
                'THURSDAY' => ['thursday',i::__('quinta'),i::__('qui')],
                'FRIDAY' => ['friday',i::__('sexta'),i::__('sex')],
                'SATURDAY' => ['saturday',i::__('sábado'),i::__('sabado'),i::__('sabado'),i::__('sab')],
                'SUNDAY' => ['sunday',i::__('domingo'),i::__('dom')],
                'PRICE' => ['price',i::__('preço'),i::__('preco')],
                'AVATAR' => ['avatar'],
                'HEADER' => ['header','banner'],
                'GALLERY' => ['gallery',i::__('galeria')],
                'DOWNLOADS' => ['downloads'],
                'VIDEOS' => [i::__('videos')],
                'LINKS' => ['links']
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
