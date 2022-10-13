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
                'SUBTITLE' => ['subtitle', i::__('subtítulo')],
                'SHORT_DESCRIPTION' => ['short_description', i::__('descrição curta'), i::__('descrição_curta')],
                'DESCRIPTION' => ['description', i::__('descrição')],
                'SITE' => ['site'],
                'LIBRAS_TRANSLATION' => [i::__('libras_translation'), i::__('tradução libras'), i::__('tradução_libras')],
                'AUDIO_DESCRIPTION' => [i::__('audio_descricao'), i::__('audio descrição'), i::__('audio_descrição')],
                'FACEBOOK' => ['facebook'],
                'INSTAGRAM' => ['instagram'],
                'TWITTER' => ['twitter'],
                'YOUTUBE' => ['youtube'],
                'LINKEDIN' => ['linkedin'],
                'SPOTIFY' => ['spotify'],
                'PINTEREST' => ['pinterest'],
                'EVENT_ATTENDANCE' => [i::__('total_de_publico'), i::__('total de publico')],
                'INSCRICOES' => [i::__('inscricoes'), i::__('inscrições')],
                'CLASSIFICATION' => ['classification', 'rating',i::__('clasificação etária'), i::__('faixa etária'), i::__('classificação')],
                'LANGUAGE' => ['language',i::__('línguagem')],
                'PROJECT' => ['project',i::__('projeto')],
                'OWNER' => ['owner',i::__('proprietário')],
                'SPACE' => ['space',i::__('espaço')],
                'STARTS_AT' => ['starts_on', i::__('data inicial')],
                'ENDS_AT' => ['ends_on',i::__('data final')],
                'FREQUENCY' => ['frequency',i::__('frequência')],
                'STARTS_ON' => ['starts_at',i::__('hora inicial')],
                'ENDS_ON' => ['ends_at','hora final'],
                'MODAY' => ['moday',i::__('segunda'),i::__('seg')],
                'TUESDAY' => ['tuesday',i::__('terça'),i::__('ter')],
                'WEDNESDAY' => ['wednesday',i::__('quarta'),i::__('qua')],
                'THURSDAY' => ['thursday',i::__('quinta'),i::__('qui')],
                'FRIDAY' => ['friday',i::__('sexta'),i::__('sex')],
                'SATURDAY' => ['saturday',i::__('sábado'),i::__('sab')],
                'SUNDAY' => ['sunday',i::__('domingo'),i::__('dom')],
                'PRICE' => ['price',i::__('preço')],
                'AVATAR' => ['avatar'],
                'HEADER' => ['header','banner'],
                'GALLERY' => ['gallery', i::__('galeria')],
                'DOWNLOADS' => ['downloads'],
                'VIDEOS' => [i::__('videos')],
                'LINKS' => ['links'],
            ],
            "csv_header_example" => [
                i::__('NOME'),
                i::__('SUBTITULO'),
                i::__('DESCRICAO_CURTA'),
                i::__('DESCRICAO_LONGA'),
                i::__('SITE'),
                i::__('TRADUCAO_LIBRAS'),
                i::__('AUDIO_DESCRICAO'),
                i::__('FACEBOOK'),
                i::__('INSTAGRAN'),
                i::__('TWITTER'),
                i::__('YOUTUBE'),
                i::__('LINKDIN'),
                i::__('SPOTIFY'),
                i::__('PINTEREST'),
                i::__('TOTAL_DE_PUBLICO'),
                i::__('INSCRIÇÕES'),
                i::__('CLASSIFICACAO_ETARIA'),
                i::__('LINGUAGEM'),
                i::__('PROJETO'),
                i::__('PROPIETARIO'),
                i::__('ESPACO'),
                i::__('HORA_INICIAL'),
                i::__('HORA_FINAL'),
                i::__('FREQUENCIA'),
                i::__('DATA_INICIAL'),
                i::__('DATA_FINAL'),
                i::__('SUGUNDA'),
                i::__('TERCA'),
                i::__('QUARTA'),
                i::__('QUINTA'),
                i::__('SEXTA'),
                i::__('SABADO'),
                i::__('DOMINGO'),
                i::__('PRECO'),
                i::__('AVATAR'),
                i::__('BANNER'),
                i::__('GALERIA'),
                i::__('DOWNLOADS'),
                i::__('VIDEOS'),
                i::__('LINKS'),

            ],
        ];

        parent::__construct($config);

    }

    function _init()
    {
        $app = App::i();

        $self = $this;

        $app->view->enqueueStyle('app','assets-file','css/eventimporter.css');
        
        $app->hook('template(panel.events.tabs-contents):end', function() use($app, $self) {
            $enabled = $self->config['enabled'];
            if($enabled()){
                /** @var Theme $this */
                $this->controller = $app->controller('agent');
                $this->part('upload-csv-event',['entity' => $app->user->profile]);
                $this->controller = $app->controller('panel');
            }
        });

        $app->hook('template(panel.events.tab-arquivo):after', function() use($app, $self) {
            $this->part('tab',['id' => "event-importer", "label" => "Importação de eventos"]);
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
