<?php

namespace EventImporter;

use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\Definitions;
use MapasCulturais\Themes;

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
                i::__('todos os dias') => 'daily',
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
            'use_endson' => [i::__('semanal'),i::__('diariamente'),'weekly','daily'],
            'clear_ocurrence_ref' => [i::__('limpar ocorrencias'),i::__('apagar ocorrencias'), i::__('zerar ocorrencias'), i::__('apagar'), i::__('limpar'), 'clear'],
            'use_week_days' => [i::__('semanal'), 'weekly'],
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
                'DOWNLOADS' => 'downloads',
            ],
            'metalists_import' => [
                'VIDEOS',
                'LINKS'
            ],
            "header_default" => [
                'SEAL_ID' => [i::__('id_selo'),i::__('id_selos'),i::__('selo'),'seal','seal_id','id_seal'],
                'EVENT_ID' => [i::__('id_evento') ,i::__('id evento') ,i::__('evento_id'), i::__('evento'),'id', 'event_id', 'event'],
                'NAME' => [i::__('nome'),'name'],
                'SUBTITLE' => [i::__('subtítulo'), 'subtitle'],
                'SHORT_DESCRIPTION' => [i::__('descrição_curta'), 'short_description', i::__('descrição curta')],
                'LONG_DESCRIPTION' => [i::__('descrição_longa'), 'long_description', i::__('descrição longa')],
                'SITE' => ['site'],
                'LIBRAS_TRANSLATION' => [i::__('tradução_libras'), i::__('libras_translation'), i::__('tradução libras')],
                'MORE_INFORMATION' => [i::__('mais_informações'), i::__('mais informações'), i::__('mais info'), 'more_information', i::__('info')],
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
                'CLASSIFICATION' => [i::__('faixa etária'),'classification', 'rating',i::__('clasificação etária'), i::__('classificação'),i::__('faixa_etária'),i::__('todos os dias')],
                'TAGS' => ['tags', 'tag'],
                'LANGUAGE' => [i::__('línguagem'), 'language'],
                'PROJECT' => [i::__('projeto'), 'project'],
                'OWNER' => [i::__('proprietário'),'owner'],
                'SPACE' => [i::__('espaço'),'space'],
                'STARTS_ON' => [i::__('data inicial'), 'starts_on'],
                'ENDS_ON' => [i::__('data final'), 'ends_on'],
                'FREQUENCY' => [i::__('frequência'), 'frequency'],
                'STARTS_AT' => [i::__('hora_inicial'), i::__('hora inicial'), 'starts_at'],
                'ENDS_AT' => [i::__('hora_final'),i::__('hora final'), 'ends_at'],
                'MODAY' => [i::__('segunda'),'moday',i::__('seg')],
                'TUESDAY' => [i::__('terça'),'tuesday',i::__('ter')],
                'WEDNESDAY' => [i::__('quarta'),'wednesday',i::__('qua')],
                'THURSDAY' => [i::__('quinta'),'thursday',i::__('qui')],
                'FRIDAY' => [i::__('sexta'),'friday',i::__('sex')],
                'SATURDAY' => [i::__('sábado'),'saturday',i::__('sab')],
                'SUNDAY' => [i::__('domingo'),'sunday',i::__('dom')],
                'PRICE' => [i::__('preço'), 'price'],
                'PRICE_INFO' => [i::__('mais_informacoes_sobre_a_entrada'),i::__('mais_informações_sobre_a_entrada'), 'priceinfo'],
                'AVATAR' => ['avatar'],
                'HEADER' => ['banner', 'header'],
                'GALLERY' => [i::__('galeria'), 'gallery'],
                'DOWNLOADS' => ['downloads'],
                'VIDEOS' => [i::__('videos')],
                'LINKS' => ['links'],
            ],
            'fromToEntity' => [
                'event' => [
                    'EVENT_ID' => "id",
                    'NAME' => 'name' ,
                    'SUBTITLE' => 'subTitle' ,
                    'SITE' => 'site' ,
                    'FACEBOOK' => 'facebooK',
                    'TWITTER' => 'twitte',
                    'INSTAGRAM' => 'instagram' ,
                    'YOUTUBE' => 'youtube' ,
                    'LINKEDIN' => 'linkedin' ,
                    'SPOTIFY' => 'spotify' ,
                    'PINTEREST' => 'pinterest' ,
                    'INSCRICOES' => 'registrationInfo' ,
                    'SHORT_DESCRIPTION' => 'shortDescription' ,
                    'LONG_DESCRIPTION' => 'longDescription' ,
                    'CLASSIFICATION' => 'classificacaoEtaria',
                    'PROJECT' => 'projectId' ,
                    'EVENT_ATTENDANCE' => 'event_attendanc',
                    'LIBRAS_TRANSLATION' => 'traducaoLibra',
                    'MORE_INFORMATION' => 'telefonePublico',
                    'AUDIO_DESCRIPTION' => 'descricaoSonora' ,
                    'OWNER' => "owner",
                    'SPACE' => "space",
                    'TAGS' => "tag",
                    'LANGUAGE' => "linguagem"
                ]
            ],
            "header_example" => [
                i::__('SEAL ID') => [
                    i::__('PREENCHER O ID DO SELO, CASO QUEIRA APLICAR UM SELO NO EVENTO - garantir que o selo esteja cadastrado'),
                    i::__('1')
                ],
                i::__('ID_EVENTO') => [
                    i::__('PREENCHER SOMENTE CASO QUEIRA EDITAR UM EVENTO EXISTENTE - Informar ID do evento'),
                    i::__('1')
                ],
                i::__('NOME') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO - Informar nome do evento'),
                    i::__('Show da banda O Tranco')
                ],
                i::__('SUBTITULO') => [
                    i::__('Informar subtítulo do evento'),
                    i::__('Turnê estadual')
                ],
                i::__('DESCRICAO_CURTA') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO - Breve descrição com no máximo 400 caractéres'),
                    i::__("Texto breve falando sobre o evento")
                ],
                i::__('DESCRICAO_LONGA') => [
                    i::__('Descrição mais detalhada'),
                    i::__("Texto mais detalhado do evento")
                ],
                i::__('SITE') => [
                    i::__('Informar o site do evento no formato https://site.com.br'),
                    i::__('https://rockinrio.com/')
                ],
                i::__('TRADUCAO_LIBRAS') => [
                    i::__('Informar se o evento conta com tradução por libras usando SIM ou NÃO'),
                    i::__('Sim')
                ],
                i::__('MAIS_INFORMACOES') => [
                    i::__('Informar número de telefone para mais informações'),
                    i::__('99 99999-9999')
                ],
                i::__('AUDIO_DESCRICAO')=> [
                    i::__('informar se o evento conta com descrição por audio usando SIM ou NÂO'),
                    i::__('Não')
                ],
                i::__('FACEBOOK') => [
                    i::__('Informar o link do Facebook do evento'),
                    i::__('https://facebook.com.br/evento')
                ],
                i::__('INSTAGRAM') => [
                    i::__('Informar o link do Instagram do evento'),
                    i::__('https://instagram.com.br/evento')
                ],
                i::__('TWITTER') => [
                    i::__('Informar o link do Twitter do evento'),
                    i::__('https://twitter.com.br/evento')
                ],
                i::__('YOUTUBE') => [
                    i::__('Informar o link do Youtube do evento'),
                    i::__('https://youtube.com.br/evento')
                ],
                i::__('LINKEDIN') => [
                    i::__('Informar o link do Linkedin do evento'),
                    i::__('https://linkedin.com.br/evento')
                ],
                i::__('SPOTIFY') => [
                    i::__('Informar o link do Spotify do evento'),
                    i::__('https://spotify.com.br/evento')
                ],
                i::__('PINTEREST') => [
                    i::__('Informar o link do Pinterest do evento'),
                    i::__('https://pinterest.com.br/evento')
                ],
                i::__('TOTAL_DE_PUBLICO')  => [
                    i::__('Informar o número que corresponde ao total de público que o evento suporta'),
                    i::__('100')
                ],
                i::__('INSCRIÇÕES') => [
                    i::__('texto livre'),
                    i::__('Lorem ipsum dolor sit amet, consectetur adipiscing elit.')
                ],
                i::__('FAIXA_ETARIA')  => [
                    i::__("PREENCHIMENTO OBRIGATÓRIO - Classificação de idade do evento usando as opções Livre, 10 anos, 12 anos, 14 anos, 16 anos, 18 anos"),
                    i::__('Livre')
                ],
                i::__('LINGUAGEM') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO - Informar as linguagens do evento separando-as com ponto e virgula, ;'),
                    i::__("Teatro;Música Popular;Livro e Literatura"),
                ],
                i::__('TAGS') => [
                    i::__('Informar as tags do evento separando-as com ponto e virgula, ;'),
                    i::__("Cultura;Musica;Arte")
                ],
                i::__('PROJETO') => [
                    i::__('Informar o nome ou ID do projeto que o evento esta vinculado'),
                    i::__('Projeto Rock2022')
                ],
                i::__('PROPRIETARIO') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO - Informar ID do agente reponsável pelo evento. Esse campod eve ser numérico'),
                    i::__('6526')
                ],
                i::__('ESPACO')  => [
                    i::__('Informar o nome ou ID do espaço que o evento esta vinculado'),
                    i::__('8965')
                ],
                i::__('HORA_INICIAL') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO CASO INFORME A CALUNA ESPAÇO OU FREQUEÊNCIA - Informar inícial do evento no formato HH:MM'),
                    i::__('12:00')
                ],
                i::__('HORA_FINAL') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO CASO INFORME A CALUNA ESPAÇO OU FREQUEÊNCIA - Informar final do evento no formato HH:MM'),
                    i::__('13:00')
                ],
                i::__('FREQUENCIA') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO CASO INFORME A CALUNA ESPAÇO - Informar a frequência que o evento irá acontecer usando as opções todos os dias, semanal ou uma vez'),
                    i::__('semanal')
                ],
                i::__('DATA_INICIAL') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO CASO INFORME A CALUNA ESPAÇO OU FREQUEÊNCIA - Informar data inícial do evento no formato DD/MM/YYYY'),
                    i::__('01/05/2022')
                ],
                i::__('DATA_FINAL') => [
                    i::__('PREENCHIMENTO OBRIGATÓRIO CASO INFORME A CALUNA ESPAÇO OU FREQUEÊNCIA -  Informar data final do evento no formato DD/MM/YYYY'),
                    i::__('30/05/2022')
                ],
                i::__('SEGUNDA') => [
                    i::__('Caso o evento ocorra neste dia informar um dos valores a seguir (sim,x,1) caso contrario deixar vazio'),
                    i::__('sim')
                ],
                i::__('TERCA') => [
                    i::__('Caso o evento ocorra neste dia informar um dos valores a seguir (sim,x,1) caso contrario deixar vazio'),
                    i::__('1')
                ],
                i::__('QUARTA') => [
                    i::__('Caso o evento ocorra neste dia informar um dos valores a seguir (sim,x,1) caso contrario deixar vazio'),
                    i::__('x')
                ],
                i::__('QUINTA') => [
                    i::__('Caso o evento ocorra neste dia informar um dos valores a seguir (sim,x,1) caso contrario deixar vazio'),
                    ""
                ],
                i::__('SEXTA') => [
                    i::__('Caso o evento ocorra neste dia informar um dos valores a seguir (sim,x,1) caso contrario deixar vazio'),
                    ""
                ],
                i::__('SABADO') => [
                    i::__('Caso o evento ocorra neste dia informar um dos valores a seguir (sim,x,1) caso contrario deixar vazio'),
                    i::__('sim')
                ],
                i::__('DOMINGO') => [
                    i::__('Caso o evento ocorra neste dia informar um dos valores a seguir (sim,x,1) caso contrario deixar vazio'),
                    i::__('x')
                ],
                i::__('PRECO') => [
                    i::__('Informar os valores cobrados para entrada no evento com texto livre'),
                    i::__('1 KG de alimento não perecível')
                ]
                ,i::__('MAIS_INFORMACOES_SOBRE_A_ENTRADA') => [
                    i::__('Mais informações sobre a entrada'),
                    i::__('Complemento das informações de')
                ],
                i::__('AVATAR') => [
                    i::__('Informar o link da imagem que deseja colocar no avatar do evento'),
                    i::__('https://cdn.pensador.com/img/authors/ho/me/homer-simpson-l.jpg')
                ],
                i::__('BANNER') => [
                    i::__('Informar o link da imagem que deseja colocar no banner do evento'),
                    i::__('https://www.mapacultural.pe.gov.br/files/event/697/file/50117/blob-47431574e39234dccb0e2d28febcb873.png')
                ],
                i::__('GALERIA') => [
                    i::__('Informar entre colchetes [] o link e o título da imagem. Separar o link e o titulo com dois pontos : . A estrutura deve ser seguida para cada imagem que quiser inserir na galeria de imagens'),
                    i::__('[https://www.aldirblanchomolog.mapacultural.pe.gov.br/files/event/1546/images.jpeg:titulo 1] [https://www.aldirblanchomolog.mapacultural.pe.gov.br/files/event/1546/images.jpeg:titulo 2]')
                ],
                i::__('DOWNLOADS') => [
                    i::__('Informar entre colchetes [] o link  e o título do arquivo. Separar o link e o titulo com dois pontos :. A estrutura deve ser seguida para cada arquivo que quiser inserir nos downloads'),
                    i::__('[https://siloseventos.com.br/files/apresentacao-silos-eventos.pdf:arquivo 1]')
                ],
                i::__('VIDEOS') => [
                    i::__('Informar entre colchetes [] o link e título da video. Separar o link e o titulo com dois pontos :. A estrutura deve ser seguida para cada imagem que quiser inserir na galeria de vídeos'),
                    i::__('[https://www.youtube.com/watch?v=6pKJRAOqAGw:O Rappa]')
                ],
                i::__('LINKS') => [
                    i::__('Informar entre colchetes [] o link e título. Separar o link e o titulo com dois pontos :. A estrutura deve ser seguida para cada link que quiser inserir na galeria de links'),
                    i::__('[https://link1.com:descricao]')
                ],

            ],
        ];

        parent::__construct($config);

    }

    function _init()
    {
        $app = App::i();

        $self = $this;

        if ($app->view->version < 2) {
            // BaseV1
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
                $enabled = $self->config['enabled'];
                if($enabled()){
                    $this->part('tab',['id' => "event-importer", "label" => "Importação de eventos"]);
                }
            });
        } else {
            // BaseV2

            $app->hook('component(panel--entity-tabs):end', function () {
                /** @var Themes\BaseV2\Theme $this */
                if($this->controller->action == 'events') {
                    $this->part('event-importer/tab');
                }
            });
        }
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

        $this->registerAgentMetadata('event_importer_files_processed', [
            'label' => 'O arquivo ja foi processado',
            'type' => 'array',
            'private' => true,
            'default_value' => '{}',
        ]);
        
        $app->registerFileGroup(
            'agent',
            new Definitions\FileGroup(
                'event-import-file',
                ['text/csv', 'application/excel', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet', 'application/vnd.ms-excel'],
                'O arquivo não e valido'
            )
        );
    }
}
