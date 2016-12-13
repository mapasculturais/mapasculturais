<?php

namespace MapasCulturais\Themes\BaseV1;

use MapasCulturais;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Notification;
use MapasCulturais\i;
use Respect\Validation\length;

class Theme extends MapasCulturais\Theme {

    protected $_libVersions = array(
        'leaflet' => '0.7.3',
        'angular' => '1.5.5',
        'jquery' => '2.1.1',
        'jquery-ui' => '1.11.4',
        'select2' => '3.5.0',
        'magnific-popup' => '0.9.9',
        'x-editable' => 'jquery-editable-dev-1.5.2'
    );

    static function getThemeFolder() {
        return __DIR__;
    }

    protected static function _getTexts(){
        $app = App::i();
        return array(
            'site: name' => $app->config['app.siteName'],
            'site: description' => $app->config['app.siteDescription'],
            'site: in the region' => \MapasCulturais\i::__('na região'),
            'site: of the region' => \MapasCulturais\i::__('da região'),
            'site: owner' => \MapasCulturais\i::__('Secretaria'),
            'site: by the site owner' => \MapasCulturais\i::__('pela Secretaria'),
            'site: panel' => \MapasCulturais\i::__('Painel'),

            'home: title' => \MapasCulturais\i::__("Bem-vind@!"),
            'home: abbreviation' => \MapasCulturais\i::__("MC"),
            'home: colabore' => \MapasCulturais\i::__("Colabore com o Mapas Culturais"),
            'home: welcome' => \MapasCulturais\i::__("O Mapas Culturais é uma plataforma livre, gratuita e colaborativa de mapeamento cultural."),
            'home: events' => \MapasCulturais\i::__("Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente."),
            'home: agents' => \MapasCulturais\i::__("Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural da região. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita."),
            'home: spaces' => \MapasCulturais\i::__("Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais."),
            'home: projects' => \MapasCulturais\i::__("Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos."),
            'home: home_devs' => \MapasCulturais\i::__('Existem algumas maneiras de desenvolvedores interagirem com o Mapas Culturais. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Mapas Culturais é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.'),

            'search: verified results' => \MapasCulturais\i::__('Resultados Verificados'),
            'search: verified' => \MapasCulturais\i::__("Verificados"),

            'entities: My Projects' => \MapasCulturais\i::__('Meus Projetos'),
            'entities: My projects' => \MapasCulturais\i::__('Meus projetos'),
            'entities: Project' => \MapasCulturais\i::__('Projeto'),
            'entities: Projects' => \MapasCulturais\i::__('Projetos'),
            'entities: project' => \MapasCulturais\i::__('projeto'),
            'entities: projects' => \MapasCulturais\i::__('projetos'),
            'entities: Projects of the agent' => \MapasCulturais\i::__('Projetos do agente'),

            'entities: My Events' => \MapasCulturais\i::__('Meus Eventos'),
            'entities: My events' => \MapasCulturais\i::__('Meus eventos'),
            'entities: Event' => \MapasCulturais\i::__('Evento'),
            'entities: Events' => \MapasCulturais\i::__('Eventos'),
            'entities: event' => \MapasCulturais\i::__('evento'),
            'entities: events' => \MapasCulturais\i::__('eventos'),

            'entities: My Agents' => \MapasCulturais\i::__('Meus Agentes'),
            'entities: My agents' => \MapasCulturais\i::__('Meus agentes'),
            'entities: Agent' => \MapasCulturais\i::__('Agente'),
            'entities: Agents' => \MapasCulturais\i::__('Agentes'),
            'entities: agent' => \MapasCulturais\i::__('agente'),
            'entities: agents' => \MapasCulturais\i::__('agentes'),

            'entities: Spaces of the agent' => \MapasCulturais\i::__('Espaços do agente'),
            'entities: Space Description' => \MapasCulturais\i::__('Descrição do Espaço'),
            'entities: Agent children' => \MapasCulturais\i::__('Agentes'),
            'entities: My Spaces' => \MapasCulturais\i::__('Meus Espaços'),
            'entities: My spaces' => \MapasCulturais\i::__('Meus espaços'),

        	'entities: My Seals' => \MapasCulturais\i::__('Meus Selos'),
        	'entities: My seals' => \MapasCulturais\i::__('Meus selos'),
            'entities: Seals' => \MapasCulturais\i::__('Selos'),
            'entities: seals' => \MapasCulturais\i::__('selos'),
            'entities: Seal' => \MapasCulturais\i::__('Selo'),
            'entities: seal' => \MapasCulturais\i::__('selo'),

            'entities: Users and roles'=> \MapasCulturais\i::__('Usuários e papéis'),

            'entities: no registered spaces'=> \MapasCulturais\i::__('nenhum espaço cadastrado'),
            'entities: no spaces'=> \MapasCulturais\i::__('nenhum espaço'),

            'entities: Space' => \MapasCulturais\i::__('Espaço'),
            'entities: Spaces' => \MapasCulturais\i::__('Espaços'),
            'entities: space' => \MapasCulturais\i::__('espaço'),
            'entities: spaces' => \MapasCulturais\i::__('espaços'),
            'entities: parent space' => \MapasCulturais\i::__('espaço pai'),
            'entities: a space' => \MapasCulturais\i::__('um espaço'),
            'entities: the space' => \MapasCulturais\i::__('o espaço'),
            'entities: of the space' => \MapasCulturais\i::__('do espaço'),
            'entities: In this space' => \MapasCulturais\i::__('Neste espaço'),
            'entities: in this space' => \MapasCulturais\i::__('neste espaço'),
            'entities: registered spaces' => \MapasCulturais\i::__('espaços cadastrados'),
            'entities: new space' => \MapasCulturais\i::__('novo espaço'),

            'entities: Children spaces' => \MapasCulturais\i::__('Subespaços'),
            'entities: Add child space' => \MapasCulturais\i::__('Adicionar subespaço'),

            'entities: space found' => \MapasCulturais\i::__('espaço encontrado'),
            'entities: spaces found' => \MapasCulturais\i::__('espaços encontrados'),
            'entities: event found' => \MapasCulturais\i::__('evento encontrado'),
            'entities: events found' => \MapasCulturais\i::__('eventos encontrados'),
            'entities: agent found' => \MapasCulturais\i::__('agente encontrado'),
            'entities: agents found' => \MapasCulturais\i::__('agentes encontrados'),
            'entities: project found' => \MapasCulturais\i::__('projeto encontrado'),
            'entities: project found' => \MapasCulturais\i::__('projetos encontrados'),

            'roles: Super Administrator'=> \MapasCulturais\i::__('Super Administrador'),
            'roles: Super Administrators'=> \MapasCulturais\i::__('Super Administradores'),

            'roles: Administrator'=> \MapasCulturais\i::__('Administrador'),
            'roles: Administrators'=> \MapasCulturais\i::__('Administradores'),

            'roles: Staff Member'=> \MapasCulturais\i::__('Membro da equipe'),
            'roles: Staff Members'=> \MapasCulturais\i::__('Membros da equipe'),

            'taxonomies:area: name' => \MapasCulturais\i::__('Área de Atuação'),
            'taxonomies:area: select at least one' => \MapasCulturais\i::__('Selecione pelo menos uma área'),
            'taxonomies:area: select' => \MapasCulturais\i::__('Selecione as áreas'),
        );
    }

    function getSearchAgentsUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(enabled:(agent:!t),filterEntity:agent))";
    }

    function getSearchSpacesUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(enabled:(space:!t),filterEntity:space))";
    }

    function getSearchEventsUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(enabled:(event:!t),filterEntity:event))";
    }

    function getSearchProjectsUrl(){
        return App::i()->createUrl('site', 'search')."##(global:(filterEntity:project,viewMode:list))";;
    }

    function getSearchSealsUrl(){
    	return App::i()->createUrl('site', 'search')."##(global:(enabled:(seal:!t),filterEntity:seal))";
    }

    protected function _init() {
        $app = App::i();

        $app->hook('mapasculturais.body:before', function() {
            if($this->controller && ($this->controller->action == 'single' || $this->controller->action == 'edit' )): ?>
                <!--facebook compartilhar-->
                    <div id="fb-root"></div>
                    <script>(function(d, s, id) {
                      var js, fjs = d.getElementsByTagName(s)[0];
                      if (d.getElementById(id)) return;
                      js = d.createElement(s); js.id = id;
                      js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1";
                      fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>
                <!--fim do facebook-->
                <?php
            endif;
        });

        $this->jsObject['notificationsInterval'] = $app->config['notifications.interval'];

        $this->jsObject['infoboxFields'] = 'id,singleUrl,name,subTitle,type,shortDescription,terms,project.name,project.singleUrl';

        $this->jsObject['EntitiesDescription'] = [
        		"agent" => \MapasCulturais\Entities\Agent::getPropertiesMetadata(),
        		"event" => \MapasCulturais\Entities\Event::getPropertiesMetadata(),
        		"space" => \MapasCulturais\Entities\Space::getPropertiesMetadata(),
        		"project" => \MapasCulturais\Entities\Project::getPropertiesMetadata(),
        		"seal" => \MapasCulturais\Entities\Seal::getPropertiesMetadata()
        ];

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->assetManager->publishAsset('css/main.css.map', 'css/main.css.map');

            $this->jsObject['assets'] = array();
            $this->jsObject['templateUrl'] = array();
            $this->jsObject['spinnerUrl'] = $this->asset('img/spinner.gif', false);

            $this->jsObject['assets']['fundo'] = $this->asset('img/fundo.png', false);
            $this->jsObject['assets']['instituto-tim'] = $this->asset('img/instituto-tim-white.png', false);
            $this->jsObject['assets']['verifiedIcon'] = $this->asset('img/verified-icon.png', false);
            $this->jsObject['assets']['avatarAgent'] = $this->asset('img/avatar--agent.png', false);
            $this->jsObject['assets']['avatarSeal'] = $this->asset('img/avatar--seal.png', false);
            $this->jsObject['assets']['avatarSpace'] = $this->asset('img/avatar--space.png', false);
            $this->jsObject['assets']['avatarEvent'] = $this->asset('img/avatar--event.png', false);
            $this->jsObject['assets']['avatarProject'] = $this->asset('img/avatar--project.png', false);

            $this->jsObject['isEditable'] = $this->isEditable();
            $this->jsObject['isSearch'] = $this->isSearch();

            $this->jsObject['angularAppDependencies'] = [
                'entity.module.relatedAgents',
            	'entity.module.relatedSeals',
                'entity.module.changeOwner',
                'entity.directive.editableMultiselect',
                'entity.directive.editableSingleselect',

                'mc.directive.singleselect',
                'mc.directive.multiselect',
                'mc.directive.editBox',
                'mc.directive.mcSelect',
                'mc.module.notifications',
                'mc.module.findEntity',

                'ngSanitize',
            ];

            $this->jsObject['mapsDefaults'] = array(
                'zoomMax' => $app->config['maps.zoom.max'],
                'zoomMin' => $app->config['maps.zoom.min'],
                'zoomDefault' => $app->config['maps.zoom.default'],
                'zoomPrecise' => $app->config['maps.zoom.precise'],
                'zoomApproximate' => $app->config['maps.zoom.approximate'],
                'includeGoogleLayers' => $app->config['maps.includeGoogleLayers'],
                'latitude' => $app->config['maps.center'][0],
                'longitude' => $app->config['maps.center'][1]
            );

            $this->jsObject['mapMaxClusterRadius']          = $app->config['maps.maxClusterRadius'];
            $this->jsObject['mapSpiderfyDistanceMultiplier']= $app->config['maps.spiderfyDistanceMultiplier'];
            $this->jsObject['mapMaxClusterElements']        = $app->config['maps.maxClusterElements'];
            $this->jsObject['mapGeometryFieldQuery']        = $app->config['maps.geometryFieldQuery'];

            $this->jsObject['labels'] = array(
                'agent' => \MapasCulturais\Entities\Agent::getPropertiesLabels(),
                'project' => \MapasCulturais\Entities\Project::getPropertiesLabels(),
                'event' => \MapasCulturais\Entities\Event::getPropertiesLabels(),
                'space' => \MapasCulturais\Entities\Space::getPropertiesLabels(),
                'registration' => \MapasCulturais\Entities\Registration::getPropertiesLabels(),
                'seal' => \MapasCulturais\Entities\Seal::getPropertiesLabels()

            );

            $this->jsObject['routes'] = $app->config['routes'];

            $this->addDocumentMetas();
            $this->includeVendorAssets();
            $this->includeCommonAssets();
            $this->_populateJsObject();
        });

        $app->hook('view.render(<<agent|space|project|event|seal|saas>>/<<single|edit|create>>):before', function() {
            $this->jsObject['assets']['verifiedSeal'] = $this->asset('img/verified-seal.png', false);
            $this->jsObject['assets']['unverifiedSeal'] = $this->asset('img/unverified-seal.png', false);
            $this->assetManager->publishAsset('img/verified-seal-small.png', 'img/verified-seal-small.png');
        });

        $app->hook('entity(<<agent|space>>).<<insert|update>>:before', function() use ($app) {

            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('type', 'type');
            $rsm->addScalarResult('name', 'name');

            $x = $this->location->longitude;
            $y = $this->location->latitude;

            $strNativeQuery = "SELECT type, name FROM geo_division WHERE ST_Contains(geom, ST_Transform(ST_GeomFromText('POINT($x $y)',4326),4326))";

            $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);

            $divisions = $query->getScalarResult();

            foreach ($app->getRegisteredGeoDivisions() as $d) {
                $metakey = $d->metakey;
                $this->$metakey = '';
            }

            foreach ($divisions as $div) {
                $metakey = 'geo' . ucfirst($div['type']);
                $this->$metakey = $div['name'];
            }
        });

        $app->hook('entity(<<agent|space|event|project|seal>>).insert:after', function() use($app){
            if(!$app->user->is('guest')){
                $user = $this->ownerUser;
                $app->createAndSendMailMessage([
                    'from' => $app->config['mailer.from'],
                    'to' => $user->email,
                    'subject' => sprintf(\MapasCulturais\i::__("Novo %s registrado"), $this->entityTypeLabel()),
                    'body' => sprintf(\MapasCulturais\i::__("Criado(a) %s de nome %s pelo usuário %s na instalação %s em %s."), $this->entityTypeLabel(), $this->name, $app->user->profile->name, $this->origin_site, $this->createTimestamp->format('d/m/Y - H:i'))
                ]);
            }
        });

        // sempre que insere uma imagem cria o avatarSmall
        $app->hook('entity(<<agent|space|event|project|seal>>).file(avatar).insert:after', function() {
            $this->transform('avatarSmall');
            $this->transform('avatarMedium');
            $this->transform('avatarBig');
        });

        $app->hook('entity(<<agent|space|event|project|seal>>).file(header).insert:after', function() {
            $this->transform('header');
        });

        $app->hook('entity(<<agent|space|event|project|seal>>).file(gallery).insert:after', function() {
            $this->transform('galleryThumb');
            $this->transform('galleryFull');
        });

        $app->hook('entity(event).save:before', function() {
            $this->type = 1;
        });


        $app->hook('repo(<<*>>).getIdsByKeywordDQL.join', function(&$joins, $keyword) {
            $taxonomy = App::i()->getRegisteredTaxonomyBySlug('tag');

            $class = $this->getClassName();

            $joins .= "LEFT JOIN e.__termRelations tr
                LEFT JOIN
                        tr.term
                            t
                        WITH
                            t.taxonomy = '{$taxonomy->id}'";
        });

        $app->hook('repo(<<*>>).getIdsByKeywordDQL.where', function(&$where, $keyword) {
            $where .= " OR unaccent(lower(t.term)) LIKE unaccent(lower(:keyword)) ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.join', function(&$joins, $keyword) {
            $joins .= " LEFT JOIN e.project p
                    LEFT JOIN e.__metadata m
                    WITH
                        m.key = 'subTitle'
                ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.where', function(&$where, $keyword) use($app) {
            $projects = $app->repo('Project')->findByKeyword($keyword);
            $project_ids = [];
            foreach($projects as $project){
                $project_ids = array_merge($project_ids, [$project->id], $project->getChildrenIds());
            }
            if($project_ids){
                $where .= " OR p.id IN ( " . implode(',', $project_ids) . ")";
            }
            $where .= " OR unaccent(lower(m.value)) LIKE unaccent(lower(:keyword))";
        });
        
        $theme = $this;
        $app->hook("GET(site.address_by_postalcode)", function() use($app, $theme) {
            
            $response = $theme->getAddressByPostalCode($app->request->get('postalcode'));
            if ($response['success'] === true) {
                echo json_encode($response);
            } else {
                $app->halt(403, $response['error_msg']);
            }
            
        });
    }
    
    
    /*
     * This methods tries to fill the address fields using the postal code
     * 
     * By default it relies on brazilian CEP, but you can override this methods
     * to use another API.
     * 
     * It should return an Arrau with an item success set to true or false.
     * 
     * If true, it has to return the following fields.
     * Note: lat & lon are optional, they are not beeing used yet but will probably be soon
     * 
     * response example:
     * 
     * [
     *    'success' => true,
     *      'lat' => $json->latitude,
     *      'lon' => $json->longitude,
     *      'streetName' => $json->logradouro,
     *      'neighborhood' => $json->bairro,
     *      'city' => $json->cidade,
     *      'state' => $json->estado
     * ]
     * 
     */ 
    function getAddressByPostalCode($postalCode) {
        $app = App::i();
        if ($app->config['cep.token']) {
            $cep = str_replace('-', '', $postalCode);
            // $url = 'http://www.cepaberto.com/api/v2/ceps.json?cep=' . $cep;
            $url = sprintf($app->config['cep.endpoint'], $cep);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            if ($app->config['cep.token_header']) {
                // curl_setopt($ch, CURLOPT_HTTPHEADER, array('Authorization: Token token="' . $app->config['cep.token'] . '"'));
                curl_setopt($ch, CURLOPT_HTTPHEADER, array(sprintf($app->config['cep.token_header'], $app->config['cep.token'])));
            }
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            $output = curl_exec($ch);
            $json = json_decode($output);
            if (isset($json->logradouro)) { 
                $response = [
                    'success' => true,
                    'lat' => $json->latitude,
                    'lon' => $json->longitude,
                    'streetName' => $json->logradouro,
                    'neighborhood' => $json->bairro,
                    'city' => $json->cidade,
                    'state' => $json->estado
                ];
            } else {
                $response = [
                    'success' => false,
                    'error_msg' => 'Falha a buscar endereço'
                ];
            }
        } else {
            $response = [
                'success' => false,
                'error_msg' => 'No token for CEP'
            ];
        }
        
        return $response;
    }

    function register() {
        $app = App::i();
        $geoDivisionsHierarchyCfg = $app->config['app.geoDivisionsHierarchy'];
        foreach ($geoDivisionsHierarchyCfg as $slug => $name) {
            foreach (array('MapasCulturais\Entities\Agent', 'MapasCulturais\Entities\Space') as $entity_class) {
                $entity_types = $app->getRegisteredEntityTypes($entity_class);

                foreach ($entity_types as $type) {
                    $metadata = new \MapasCulturais\Definitions\Metadata('geo' . ucfirst($slug), array('label' => $name));
                    $app->registerMetadata($metadata, $entity_class, $type->id);
                }
            }
        }
    }

    function head() {
        parent::head();

        $app = App::i();

        $this->printStyles('vendor');
        $this->printStyles('app');

        $app->applyHook('mapasculturais.styles');

        $this->_printJsObject();

        $this->printScripts('vendor');
        $this->printScripts('app');

        $app->applyHook('mapasculturais.scripts');
    }

    function addDocumentMetas() {
        $app = App::i();
        $entity = $this->controller->requestedEntity;

        $site_name = $this->dict('site: name', false);
        $title = $app->view->getTitle($entity);
        $image_url = $app->view->asset('img/share.png', false);
        if ($entity) {
            $description = $entity->shortDescription ? $entity->shortDescription : $title;
            if ($entity->avatar)
                $image_url = $entity->avatar->transform('avatarBig')->url;
        }else {
            $description = $this->dict('site: description', false);
        }
        // for responsive
        $this->documentMeta[] = array("name" => 'viewport', 'content' => 'width=device-width, initial-scale=1, maximum-scale=1.0');
        // for google
        $this->documentMeta[] = array("name" => 'description', 'content' => $description);
        $this->documentMeta[] = array("name" => 'keywords', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'author', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'copyright', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'application-name', 'content' => $site_name);

        // for google+
        $this->documentMeta[] = array("itemprop" => 'author', 'content' => $title);
        $this->documentMeta[] = array("itemprop" => 'description', 'content' => $description);
        $this->documentMeta[] = array("itemprop" => 'image', 'content' => $image_url);

        // for twitter
        $this->documentMeta[] = array("name" => 'twitter:card', 'content' => $site_name);
        $this->documentMeta[] = array("name" => 'twitter:title', 'content' => $title);
        $this->documentMeta[] = array("name" => 'twitter:description', 'content' => $description);
        $this->documentMeta[] = array("name" => 'twitter:image', 'content' => $image_url);

        // for facebook
        $this->documentMeta[] = array("property" => 'og:title', 'content' => $title);
        $this->documentMeta[] = array("property" => 'og:type', 'content' => 'article');
        $this->documentMeta[] = array("property" => 'og:image', 'content' => $image_url);
        $this->documentMeta[] = array("property" => 'og:image:url', 'content' => $image_url);
        $this->documentMeta[] = array("property" => 'og:description', 'content' => $description);
        $this->documentMeta[] = array("property" => 'og:site_name', 'content' => $site_name);

        if ($entity) {
            $this->documentMeta[] = array("property" => 'og:url', 'content' => $entity->singleUrl);
            $this->documentMeta[] = array("property" => 'og:published_time', 'content' => $entity->createTimestamp->format('Y-m-d'));

            // @TODO: modified time is not implemented
            // $this->documentMeta[] = array( "property" => 'og:modified_time',   'content' => $entity->modifiedTimestamp->format('Y-m-d'));
        }
    }

    function includeVendorAssets() {
        $versions = $this->_libVersions;

        $this->enqueueStyle('vendor', 'x-editable', "vendor/x-editable-{$versions['x-editable']}/css/jquery-editable.css", array('select2'));

        $this->enqueueScript('vendor', 'mustache', 'vendor/mustache.js');

        $this->enqueueScript('vendor', 'jquery', "vendor/jquery-{$versions['jquery']}.js");
        $this->enqueueScript('vendor', 'jquery-slimscroll', 'vendor/jquery.slimscroll.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-form', 'vendor/jquery.form.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-mask', 'vendor/jquery.mask.js', array('jquery'));
        $this->enqueueScript('vendor', 'purl', 'vendor/purl/purl.js', array('jquery'));

        // select 2
        $this->enqueueStyle('vendor', 'select2', "vendor/select2-{$versions['select2']}/select2.css");
        $this->enqueueScript('vendor', 'select2', "vendor/select2-{$versions['select2']}/select2.js", array('jquery'));

        $this->enqueueScript('vendor', 'select2-BR', 'vendor/select2_locale_pt-BR-edit.js', array('select2'));

        $this->enqueueScript('vendor', 'poshytip', 'vendor/x-editable-jquery-poshytip/jquery.poshytip.js', array('jquery'));
        $this->enqueueScript('vendor', 'x-editable', "vendor/x-editable-{$versions['x-editable']}/js/jquery-editable-poshytip.js", array('jquery', 'poshytip', 'select2'));

        //Leaflet -a JavaScript library for mobile-friendly maps
        $this->enqueueStyle('vendor', 'leaflet', "vendor/leaflet/lib/leaflet-{$versions['leaflet']}/leaflet.css");
        $this->enqueueScript('vendor', 'leaflet', "vendor/leaflet/lib/leaflet-{$versions['leaflet']}/leaflet-src.js");

        //Leaflet Vector Layers
        $this->enqueueScript('vendor', 'leaflet-vector-layers', 'vendor/leaflet-vector-layers/dist/lvector.js', array('leaflet'));

        //Conjuntos de Marcadores
        $this->enqueueStyle('vendor', 'leaflet-marker-cluster', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.css', array('leaflet'));
        $this->enqueueStyle('vendor', 'leaflet-marker-cluster-d', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.Default.css', array('leaflet-marker-cluster'));
        $this->enqueueScript('vendor', 'leaflet-marker-cluster', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/leaflet.markercluster-src.js', array('leaflet'));

        //Controle de Full Screen
        $this->enqueueStyle('vendor', 'leaflet-fullscreen', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.css', array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-fullscreen', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.js', array('leaflet'));

        //Leaflet Label Plugin
        $this->enqueueScript('vendor', 'leaflet-label', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.label-master/dist/leaflet.label-src.js', array('leaflet'));

        //Leaflet Draw
        $this->enqueueStyle('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw.css', array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw-src.js', array('leaflet'));

        // Google Maps API only needed in site/search and space, agent and event singles
        if(preg_match('#site|space|agent|event#',    $this->controller->id) && preg_match('#search|single|edit|create#', $this->controller->action)){
            $this->enqueueScript('vendor', 'google-maps-api', '//maps.google.com/maps/api/js?v=3.2&sensor=false');
        }

        //Leaflet Plugins
        $this->enqueueScript('vendor', 'leaflet-google-tile', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-plugins-master/layer/tile/Google.js', array('leaflet'));

        $this->enqueueStyle('vendor', 'magnific-popup', "vendor/Magnific-Popup-{$versions['magnific-popup']}/magnific-popup.css");
        $this->enqueueScript('vendor', 'magnific-popup', "vendor/Magnific-Popup-{$versions['magnific-popup']}/jquery.magnific-popup.js", array('jquery'));

        $this->enqueueScript('vendor', 'momentjs', 'vendor/moment.js');
        $this->enqueueScript('vendor', 'momentjs-pt-br', 'vendor/moment.pt-br.js', array('momentjs'));

        $this->enqueueScript('vendor', 'jquery-ui', "vendor/jquery-ui-{$versions['jquery-ui']}/jquery-ui.js", array('jquery'));
        $this->enqueueScript('vendor', 'jquery-ui-datepicker-pt-BR', "vendor/jquery-ui-{$versions['jquery-ui']}/datepicker-pt-BR.js", array('jquery-ui'));

        $this->enqueueScript('vendor', 'angular', "vendor/angular-{$versions['angular']}/angular.js", array('jquery', 'jquery-ui-datepicker-pt-BR'));
        $this->enqueueScript('vendor', 'angular-sanitize', "vendor/angular-{$versions['angular']}/angular-sanitize.js", array('angular'));

        $this->enqueueScript('vendor', 'angular-rison', '/vendor/angular-rison.js', array('angular'));
        $this->enqueueScript('vendor', 'ng-infinite-scroll', '/vendor/ng-infinite-scroll/ng-infinite-scroll.js', array('angular'));

        $this->enqueueScript('vendor', 'angular-ui-date', '/vendor/ui-date-master/src/date.js', array('jquery-ui-datepicker-pt-BR', 'angular'));
        $this->enqueueScript('vendor', 'angular-ui-sortable', '/vendor/ui-sortable/sortable.js', array('jquery-ui', 'angular'));
        $this->enqueueScript('vendor', 'angular-checklist-model', '/vendor/checklist-model/checklist-model.js', array('jquery-ui', 'angular'));
    }

    function includeCommonAssets() {
        $this->getAssetManager()->publishFolder('fonts/');

        $this->enqueueStyle('app', 'main', 'css/main.css');

        $this->enqueueScript('app', 'tim', 'js/tim.js');
        $this->localizeScript('tim', [
            'previous' => \MapasCulturais\i::__('Anterior'),
            'next' => \MapasCulturais\i::__('Próxima'),
            /* Translators: Count of the photo gallery slideshow: 2 of 10 */
            'counter' => \MapasCulturais\i::__('%curr% de %total%'),
        ]);
        $this->enqueueScript('app', 'mapasculturais', 'js/mapasculturais.js', array('tim'));
        $this->localizeScript('mapas', [
            'agente'    => \MapasCulturais\i::__('agente'),
            'espaço'    => \MapasCulturais\i::__('espaço'),
            'evento'    => \MapasCulturais\i::__('evento'),
            'projeto'   => \MapasCulturais\i::__('projeto'),
            'selo'      => \MapasCulturais\i::__('selo'),
            'Enviar'    => \MapasCulturais\i::__('Enviar'),
            'Cancelar'  => \MapasCulturais\i::__('Cancelar')
        ]);

        $this->enqueueScript('app', 'mapasculturais-customizable', 'js/customizable.js', array('mapasculturais'));
        
        // This replaces the default geocoder with the google geocoder
        if (App::i()->config['app.useGoogleGeocode'])
            $this->enqueueScript('app', 'google-geocoder', 'js/google-geocoder.js', array('mapasculturais-customizable'));


        $this->enqueueScript('app', 'ng-mapasculturais', 'js/ng-mapasculturais.js', array('mapasculturais'));
        $this->enqueueScript('app', 'mc.module.notifications', 'js/ng.mc.module.notifications.js', array('ng-mapasculturais'));
        $this->localizeScript('moduleNotifications', [
            'error'    => \MapasCulturais\i::__('There was an error'),
        ]);
        
        
        if ($this->isEditable())
            $this->includeEditableEntityAssets();

        if (App::i()->config('mode') == 'staging')
            $this->enqueueStyle('app', 'staging', 'css/staging.css', array('main'));
    }

    function includeEditableEntityAssets() {

        $versions = $this->_libVersions;
        $this->assetManager->publishAsset('img/setinhas-editable.png');

        $this->assetManager->publishAsset("vendor/x-editable-{$versions['x-editable']}/img/clear.png", 'img/clear.png');
        $this->assetManager->publishAsset("vendor/x-editable-{$versions['x-editable']}/img/loading.gif", 'img/loading.gif');

        $this->assetManager->publishAsset("vendor/select2-{$versions['select2']}/select2.png", 'css/select2.png');
        $this->assetManager->publishAsset("vendor/select2-{$versions['select2']}/select2-spinner.gif", 'css/select2-spinner.gif');

        $this->enqueueScript('app', 'editable', 'js/editable.js', array('mapasculturais'));
    }

    function includeSearchAssets() {

        $this->enqueueScript('app', 'search.service.find', 'js/ng.search.service.find.js', array('ng-mapasculturais', 'search.controller.spatial'));
        $this->enqueueScript('app', 'search.service.findOne', 'js/ng.search.service.findOne.js', array('ng-mapasculturais', 'search.controller.spatial'));
        $this->enqueueScript('app', 'search.controller.map', 'js/ng.search.controller.map.js', array('ng-mapasculturais', 'map'));
        $this->localizeScript('controllerMap', [
            /* Translators: serach results. Eventos encontrados no espaço {nome do espaço} */
            'eventsFound'    => \MapasCulturais\i::__('Eventos encontrados no espaço'),
        ]);
        
        $this->enqueueScript('app', 'search.controller.spatial', 'js/ng.search.controller.spatial.js', array('ng-mapasculturais', 'map'));
        $this->localizeScript('controllerSpatial', [
            'tooltip.start' =>  \MapasCulturais\i::__('Clique e arraste para desenhar o círculo'),
            'tooltip.end' =>    \MapasCulturais\i::__('Solte o mouse para finalizar o desenho'),
            'title' =>          \MapasCulturais\i::__('Cancelar desenho'),
            'text' =>           \MapasCulturais\i::__('Cancelar'),
            'circle' =>         \MapasCulturais\i::__('Desenhar um círculo'),
            'radius' =>         \MapasCulturais\i::__('Raio'),
            'currentLocation' =>\MapasCulturais\i::__('Segundo seu navegador, você está aproximadamente neste ponto com margem de erro de {{errorMargin}} metros. Buscando resultados dentro de um raio de {{radius}}KM deste ponto.'),
        ]);
        
        
        $this->enqueueScript('app', 'search.app', 'js/ng.search.app.js', array('ng-mapasculturais', 'search.controller.spatial', 'search.controller.map', 'search.service.findOne', 'search.service.find'));
        $this->localizeScript('searchApp', [
            'all' => \MapasCulturais\i::__('Todos'),
            /* Translators: de uma data. Ex: *de* 12/12 a 13/12 */
            'dateFrom' => \MapasCulturais\i::__('de'),
            /* Translators: a uma data. Ex: de 12/12 *a* 13/12 */
            'dateTo' => \MapasCulturais\i::__('a')
        ]);
    }

    function includeMapAssets() {
        $app = App::i();

        $this->assetManager->publishAsset('css/main.css.map', 'css/main.css.map');

        $this->jsObject['assets']['avatarAgent'] = $this->asset('img/avatar--agent.png', false);
        $this->jsObject['assets']['avatarSpace'] = $this->asset('img/avatar--space.png', false);
        $this->jsObject['assets']['avatarEvent'] = $this->asset('img/avatar--event.png', false);
        $this->jsObject['assets']['avatarProject'] = $this->asset('img/avatar--project.png', false);
        $this->jsObject['assets']['avatarSeal'] = $this->asset('img/avatar--seal.png', false);

        $this->jsObject['assets']['iconLocation'] = $this->asset('img/icon-localizacao.png', false);
        $this->jsObject['assets']['iconFullscreen'] = $this->asset('img/icon-fullscreen.png', false);
        $this->jsObject['assets']['iconZoomIn'] = $this->asset('img/icon-zoom-in.png', false);
        $this->jsObject['assets']['iconZoomOut'] = $this->asset('img/icon-zoom-out.png', false);
        $this->jsObject['assets']['layers'] = $this->asset('img/layers.png', false);
        $this->jsObject['assets']['iconCircle'] = $this->asset('img/icon-circulo.png', false);

        $this->jsObject['assets']['pinShadow'] = $this->asset('img/pin-sombra.png', false);
        $this->jsObject['assets']['pinMarker'] = $this->asset('img/marker-icon.png', false);

        $this->jsObject['assets']['pinAgent'] = $this->asset('img/pin-agente.png', false);
        $this->jsObject['assets']['pinSpace'] = $this->asset('img/pin-espaco.png', false);
        $this->jsObject['assets']['pinEvent'] = $this->asset('img/pin-evento.png', false);

        $this->jsObject['assets']['pinAgentGroup'] = $this->asset('img/agrupador-agente.png', false);
        $this->jsObject['assets']['pinEventGroup'] = $this->asset('img/agrupador-evento.png', false);
        $this->jsObject['assets']['pinSpaceGroup'] = $this->asset('img/agrupador-espaco.png', false);
        //$this->jsObject['assets']['pinSealGroup'] = $this->asset('img/agrupador-selo.png', false);

        $this->jsObject['assets']['pinAgentEventGroup'] = $this->asset('img/agrupador-combinado-agente-evento.png', false);
        $this->jsObject['assets']['pinSpaceEventGroup'] = $this->asset('img/agrupador-combinado-espaco-evento.png', false);
        $this->jsObject['assets']['pinAgentSpaceGroup'] = $this->asset('img/agrupador-combinado-espaco-agente.png', false);
        //$this->jsObject['assets']['pinSealSpaceGroup'] = $this->asset('img/agrupador-combinado-espaco-selo.png', false);

        $this->jsObject['assets']['pinAgentSpaceEventGroup'] = $this->asset('img/agrupador-combinado.png', false);

        $this->jsObject['geoDivisionsHierarchy'] = $app->config['app.geoDivisionsHierarchy'];

        $this->enqueueScript('app', 'map', 'js/map.js');
    }

    function includeAngularEntityAssets($entity) {
        $this->jsObject['templateUrl']['editBox'] = $this->asset('js/directives/edit-box.html', false);
        $this->jsObject['templateUrl']['findEntity'] = $this->asset('js/directives/find-entity.html', false);
        $this->jsObject['templateUrl']['MCSelect'] = $this->asset('js/directives/mc-select.html', false);
        $this->jsObject['templateUrl']['multiselect'] = $this->asset('js/directives/multiselect.html', false);
        $this->jsObject['templateUrl']['singleselect'] = $this->asset('js/directives/singleselect.html', false);
        $this->jsObject['templateUrl']['editableMultiselect'] = $this->asset('js/directives/editableMultiselect.html', false);
        $this->jsObject['templateUrl']['editableSingleselect'] = $this->asset('js/directives/editableSingleselect.html', false);

        $this->enqueueScript('app', 'entity.app', 'js/ng.entity.app.js', array(
            'mapasculturais',
            'ng-mapasculturais',
            'mc.directive.multiselect',
            'mc.directive.singleselect',
            'mc.directive.editBox',
            'mc.directive.mcSelect',
            'mc.module.findEntity',
            'entity.module.relatedAgents',
        	'entity.module.relatedSeals',
            'entity.module.changeOwner',
            'entity.directive.editableMultiselect',
            'entity.directive.editableSingleselect',
        ));

        $this->enqueueScript('app', 'mc.directive.multiselect', 'js/ng.mc.directive.multiselect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.directive.singleselect', 'js/ng.mc.directive.singleselect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.directive.editBox', 'js/ng.mc.directive.editBox.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.directive.mcSelect', 'js/ng.mc.directive.mcSelect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'mc.module.findEntity', 'js/ng.mc.module.findEntity.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity.module.changeOwner', 'js/ng.entity.module.changeOwner.js', array('ng-mapasculturais'));
        $this->localizeScript('changeOwner', [
            'ownerChanged' =>  \MapasCulturais\i::__('O proprietário da entidade foi modificado'),
            'cannotChangeOwner' =>  \MapasCulturais\i::__('O proprietário da entidade não pode ser modificado'),
            'requestMessage' =>  \MapasCulturais\i::__('Sua requisição para mudança de propriedade deste {{type}} para o agente {{recipient}} foi enviada.'),
        ]);
        
        $this->enqueueScript('app', 'entity.module.project', 'js/ng.entity.module.project.js', array('ng-mapasculturais'));
        $this->localizeScript('moduleProject', [
            'selectFieldType' =>  \MapasCulturais\i::__('Selecione o tipo de campo'),
            'fieldCreated' =>  \MapasCulturais\i::__('Campo criado.'),
            'fieldRemoved' =>  \MapasCulturais\i::__('Campo removido.'),
            'changesSaved' =>  \MapasCulturais\i::__('Alterações Salvas.'),
            'attachmentCreated' =>  \MapasCulturais\i::__('Anexo criado.'),
            'attachmentRemoved' =>  \MapasCulturais\i::__('Anexo removido.'),
            'confirmAttachmentRemoved' =>  \MapasCulturais\i::__('Deseja remover este anexo?'),
            'confirmRemoveModel' =>  \MapasCulturais\i::__('Deseja remover este modelo?'),
            'modelRemoved' =>  \MapasCulturais\i::__('Modelo removido.'),
            'statusPublished' =>  \MapasCulturais\i::__('publicado'),
            'statusDraft' =>  \MapasCulturais\i::__('rascunho'),
            'publishing...' =>  \MapasCulturais\i::__('Publicando...'),
            'eventsPublished' =>  \MapasCulturais\i::__('Eventos publicados.'),
            'savingAsDraft' =>  \MapasCulturais\i::__('Tornando rascunho...'),
            'savedAsDraft' =>  \MapasCulturais\i::__('Eventos transformados em rascunho.'),
            'confirmRemoveAttachment' =>  \MapasCulturais\i::__('Deseja remover este anexo?'),
            'registrationOwnerDefault' =>  \MapasCulturais\i::__('Agente responsável pela inscrição'),
            'allStatus' =>  \MapasCulturais\i::__('Todas'),
            'pending' =>  \MapasCulturais\i::__('Pendente'),
            'invalid' =>  \MapasCulturais\i::__('Inválida'),
            'notSelected' =>  \MapasCulturais\i::__('Não selecionada'),
            'suplente' =>  \MapasCulturais\i::__('Suplente'),
            'selected' =>  \MapasCulturais\i::__('Selecionada'),
            'Draft' =>  \MapasCulturais\i::__('Rascunho'),
            'requiredLabel' =>  \MapasCulturais\i::__('Obrigatório'),
            'optionalLabel' =>  \MapasCulturais\i::__('Opcional'),
            'confirmReopen' =>  \MapasCulturais\i::__('Você tem certeza que deseja reabrir este formulário para edição? Ao fazer isso, ele sairá dessa lista.'),
            'defineVacancies' =>  \MapasCulturais\i::__('Você não definiu um número de vagas. Para selecionar essa inscrição, configure um número de vagas na aba Inscrições, em Agentes.'),
            'reachedMax' =>  \MapasCulturais\i::__('Você atingiu o limite máximo de 1 inscrição aprovada'),
            'reachedMaxPlural' =>  \MapasCulturais\i::__('Você atingiu o limite máximo de {{num}} inscrições aprovadas'),
            'limitReached' =>  \MapasCulturais\i::__('O limite de inscrições para o agente informado se esgotou.'),
            'VacanciesOver' =>  \MapasCulturais\i::__('O número de vagas da inscrição no projeto se esgotou.'),
            'needResponsible' =>  \MapasCulturais\i::__('Para se inscrever neste projeto você deve selecionar um agente responsável.'),
            'correctErrors' =>  \MapasCulturais\i::__('Corrija os erros indicados abaixo.'),
            'registrationSent' =>  \MapasCulturais\i::__('Inscrição enviada. Aguarde tela de sumário.'),
        ]);
        
        $this->enqueueScript('app', 'entity.module.relatedAgents', 'js/ng.entity.module.relatedAgents.js', array('ng-mapasculturais'));
        $this->localizeScript('relatedAgents', [
            'requestSent' =>  \MapasCulturais\i::__('Sua requisição para relacionar o agente {{agent}} foi enviada.'),
        ]);
        
        $this->enqueueScript('app', 'entity.module.relatedSeals', 'js/ng.entity.module.relatedSeals.js', array('ng-mapasculturais'));
        $this->localizeScript('relatedSeals', [
            'requestSent' =>  \MapasCulturais\i::__('Sua requisição para relacionar o selo {{seal}} foi enviada.'),
        ]);
        
        $this->enqueueScript('app', 'entity.directive.editableMultiselect', 'js/ng.entity.directive.editableMultiselect.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity.directive.editableSingleselect', 'js/ng.entity.directive.editableSingleselect.js', array('ng-mapasculturais'));

        $roles = [];
        if(!\MapasCulturais\App::i()->user->is('guest')){
            foreach(\MapasCulturais\App::i()->user->roles as $r){
                $roles[] = $r->name;
            }
        }

        $this->jsObject['roles'] = $roles;
        $this->jsObject['request']['id'] = $entity->id;
    }

    protected function _printJsObject($var_name = 'MapasCulturais', $print_script_tag = true) {

        if ($print_script_tag)
            echo "\n<script type=\"text/javascript\">\n";

        echo " var {$var_name} = " . json_encode($this->jsObject) . ';';

        if ($print_script_tag)
            echo "\n</script>\n";
    }

    function ajaxUploader($file_owner, $group_name, $response_action, $response_target, $response_template = '', $response_transform = '', $add_description_input = false, $file_types = '.jpg ou .png') {
        $this->part('ajax-uploader', array(
            'file_owner' => $file_owner,
            'file_group' => $group_name,
            'response_action' => $response_action,
            'response_target' => $response_target,
            'response_template' => $response_template,
            'response_transform' => $response_transform,
            'add_description' => $add_description_input,
            'file_types' => $file_types
        ));
    }

    function getOccurrenceFrequencies() {
        return array(
            'once' => \MapasCulturais\i::__('uma vez'),
            'daily' => \MapasCulturais\i::__('todos os dias'),
            'weekly' => \MapasCulturais\i::__('semanal'),
            'monthly' => \MapasCulturais\i::__('mensal'),
        );
    }

    protected function _populateJsObject() {
        $app = App::i();
        $this->jsObject['userId'] = $app->user->is('guest') ? null : $app->user->id;
        $this->jsObject['vectorLayersURL'] = $app->baseUrl . $app->config['vectorLayersPath'];

        $this->jsObject['request'] = array(
            'controller' => $this->controller->id,
            'action' => $this->controller->action
        );

        if (!$app->user->is('guest')) {
            $this->jsObject['notifications'] = $app->controller('notification')->apiQuery(array(
                '@select' => 'id,status,isRequest,createTimestamp,message,approveUrl,request.permissionTo.approve,request.permissionTo.reject,request.requesterUser.id',
                'user' => 'EQ(@me)',
                '@ORDER' => 'createTimestamp DESC'
            ));
        }
//        eval(\Psy\sh());
        if ($this->controller->id === 'site' && $this->controller->action === 'search'){
            $skeleton_field = [
                'fieldType' => 'checklist',
                'isInline' => true,
                'isArray' => true,
                'prefix' => '',
                'type' => 'metadata',
                'label' => '',
                'placeholder' => '',
                'filter' => [
                    'param' => '',
                    'value' => 'IN({val})'
                ]
            ];

            $filters = $this->_getFilters();
            $modified_filters = [];

            $sanitize_filter_value = function($val){
                return str_replace(',', '\\,', $val);
            };
            foreach ($filters as $key => $value) {
                $modified_filters[] = $key;
                $modified_filters[$key] = [];
                foreach ($filters[$key] as $field) {
                    $mod_field = array_merge($skeleton_field, $field);

                    if (in_array($mod_field['fieldType'], ['checklist', 'singleselect'])){
                        $mod_field['options'] = [];
                        if ($mod_field['fieldType'] == 'singleselect')
                            $mod_field['options'][] = ['value' => null, 'label' => $mod_field['placeholder']];
                        switch ($mod_field['type']) {
                            case 'metadata':
                                $data = App::i()->getRegisteredMetadataByMetakey($field['filter']['param'], "MapasCulturais\Entities\\".ucfirst($key));
                                foreach ($data->config['options'] as $meta_key => $value)
                                    $mod_field['options'][] = ['value' => $sanitize_filter_value($meta_key), 'label' => $value];
                                break;
                            case 'entitytype':
                                $types = App::i()->getRegisteredEntityTypes("MapasCulturais\Entities\\".ucfirst($key));
                                foreach ($types as $type_key => $type_val)
                                    $mod_field['options'][] = ['value' => $sanitize_filter_value($type_key), 'label' => $type_val->name];
                                $this->addEntityTypesToJs("MapasCulturais\Entities\\".ucfirst($key));
                                break;
                            case 'term':
                                $tax = App::i()->getRegisteredTaxonomyBySlug($field['filter']['param']);
                                foreach ($tax->restrictedTerms as $v)
                                    $mod_field['options'][] = ['value' => $sanitize_filter_value($v), 'label' => $v];

                                $this->addTaxonoyTermsToJs($mod_field['filter']['param']);
                                break;
                        }
                    }
                    $modified_filters[$key][] = $mod_field;
                }
            }
            $this->jsObject['filters'] = $modified_filters;
        }

        if($app->user->is('admin')) {
        	$this->jsObject['allowedFields'] = true;
        } else {
        	$this->jsObject['allowedFields'] = false;
        }
    }

    protected function _getFilters(){
        return [
            'space' => [
                [
                    'label'=> $this->dict('taxonomies:area: name', false),
                    'placeholder' => $this->dict('taxonomies:area: select', false),
                    'type' => 'term',
                    'filter' => [
                        'param' => 'area',
                        'value' => 'IN({val})'
                    ]
                ],
                [
                    'label' => 'Tipos',
                    'placeholder' => \MapasCulturais\i::__('Selecione os tipos'),
                    'type' => 'entitytype',
                    'filter' => [
                        'param' => 'type',
                        'value' => 'IN({val})'
                    ]
                ],
                [
                    'label' => \MapasCulturais\i::__('Acessibilidade'),
                    'placeholder' => \MapasCulturais\i::__('Exibir somente resultados com Acessibilidade'),
                    'fieldType' => 'checkbox',
                    'isArray' => false,
                    'filter' => [
                        'param' => 'acessibilidade',
                        'value' => 'EQ(Sim)'
                    ],
                ],
                [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => \MapasCulturais\i::__('Exibir somente resultados Verificados'),
                    'fieldType' => 'checkbox-verified',
                    'addClass' => 'verified-filter',
                    'isArray' => false,
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ],
            'agent' => [
                [
                    'label'=> \MapasCulturais\i::__('Área de Atuação'),
                    'placeholder' =>\MapasCulturais\i::__( 'Selecione as áreas'),
                    'type' => 'term',
                    'filter' => [
                        'param' => 'area',
                        'value' => 'IN({val})'
                    ],
                ],
                [
                    'label' => \MapasCulturais\i::__('Tipos'),
                    'placeholder' => \MapasCulturais\i::__('Todos'),
                    'fieldType' => 'singleselect',
                    'type' => 'entitytype',
                    // 'isArray' => false,
                    'filter' => [
                        'param' => 'type',
                        'value' => 'EQ({val})'
                    ]
                ],
                [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => \MapasCulturais\i::__('Exibir somente resultados Verificados'),
                    'fieldType' => 'checkbox-verified',
                    'addClass' => 'verified-filter',
                    'isArray' => false,
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ],
            'event' => [
                // TODO: Apply filter FromTo from configuration, removing from template "filter-field.php"
                // [
                //     'label' => ['De', 'a'],
                //     'fieldType' => 'dateFromTo',
                //     'placeholder' => '00/00/0000',
                //     'isArray' => false,
                //     'prefix' => '@',
                //     'filter' => [
                //         'param' => ['from', 'to'],
                //         'value' => ['LTE({val})', 'GTE({val})']
                //     ]
                // ],
                [
                    'label' => \MapasCulturais\i::__('Linguagem'),
                    'placeholder' => \MapasCulturais\i::__('Selecione as linguagens'),
                    'fieldType' => 'checklist',
                    'type' => 'term',
                    'filter' => [
                        'param' => 'linguagem',
                        'value' => 'IN({val})'
                    ]
                ],
                [
                    'label' => \MapasCulturais\i::__('Classificação'),
                    'placeholder' => \MapasCulturais\i::__('Selecione a classificação'),
                    'filter' => [
                        'param' => 'classificacaoEtaria',
                        'value' => 'IN({val})'
                    ]
                ],
                [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => \MapasCulturais\i::__('Exibir somente resultados Verificados'),
                    'fieldType' => 'checkbox-verified',
                    'isArray' => false,
                    'addClass' => 'verified-filter',
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ],
            'project' => [
                [
                    'label' => \MapasCulturais\i::__('Tipo'),
                    'placeholder' => \MapasCulturais\i::__('Selecione os tipos'),
                    'type' => 'entitytype',
                    'filter' => [
                        'param' => 'type',
                        'value' => 'IN({val})'
                    ]
                ],
                [
                    'label' => \MapasCulturais\i::__('Inscrições Abertas'),
                    'fieldType' => 'custom.project.ropen'
                ],
                [
                    'label' => $this->dict('search: verified results', false),
                    'tag' => $this->dict('search: verified', false),
                    'placeholder' => \MapasCulturais\i::__('Exibir somente resultados Verificados'),
                    'fieldType' => 'checkbox-verified',
                    'addClass' => 'verified-filter',
                    'isArray' => false,
                    'filter' => [
                        'param' => '@verified',
                        'value' => 'IN(1)'
                    ]
                ]
            ]
        ];
    }

    function addEntityToJs(MapasCulturais\Entity $entity){
        $this->jsObject['entity'] = [
            'id' => $entity->id,
            'ownerId' => $entity->owner->id, // ? $entity->owner->id : null,
            'ownerUserId' => $entity->ownerUser->id,
            'definition' => $entity->getPropertiesMetadata(),
            'userHasControl' => $entity->canUser('@control'),
            'canUserCreateRelatedAgentsWithControl' => $entity->canUser('createAgentRelationWithControl'),
            'status' => $entity->status,
            'object' => $entity
        ];

        if($entity->usesNested() && $entity->id){
            $this->jsObject['entity']['childrenIds'] = $entity->getChildrenIds();
        }
    }

    function addOccurrenceFrequenciesToJs() {
        $this->jsObject['frequencies'] = $this->getOccurrenceFrequencies();
    }

    function addEntityTypesToJs($entity) {

        $controller = App::i()->getControllerByEntity($entity);
        $types = $controller->types;

        usort($types, function($a, $b) {
            if ($a->name > $b->name)
                return 1;
            elseif ($a->name < $b->name)
                return -1;
            else
                return 0;
        });

        if (!isset($this->jsObject['entityTypes']))
            $this->jsObject['entityTypes'] = array();

        $this->jsObject['entityTypes'][$controller->id] = $types;
    }

    function addTaxonoyTermsToJs($taxonomy_slug) {
        $terms = App::i()->repo('Term')->getTermsAsString($taxonomy_slug);
        if (!isset($this->jsObject['taxonomyTerms']))
            $this->jsObject['taxonomyTerms'] = array();

        $this->jsObject['taxonomyTerms'][$taxonomy_slug] = $terms;
    }

    function addRelatedAgentsToJs($entity) {
        $this->jsObject['entity']['agentRelations'] = $entity->getAgentRelationsGrouped(null, $this->isEditable());
    }

    function addRelatedAdminAgentsToJs($entity) {
        $this->jsObject['entity']['agentAdminRelations'] = $entity->getAgentRelations(true);
    }

    function addRelatedSealsToJs($entity) {
    	$this->jsObject['entity']['sealRelations'] = $entity->getRelatedSeals(true, $this->isEditable());
    }

    function addSealsToJs($onlyPermited = true,$sealId = array()) {
        	$query = [];
        	$query['@select'] = 'id,name,status, singleUrl';

            if($onlyPermited) {
        		$query['@permissions'] = '@control';
        	}

        	$query['@files'] = '(avatar.avatarMedium):url';
        	$sealId = implode(',',array_unique($sealId));

        	if(count($sealId) > 0 && !empty($sealId)) {
        		$query['id'] = 'IN(' .$sealId . ')';
        	}

        	$query['@ORDER'] = 'createTimestamp DESC';

        	$app = App::i();
        	if (!$app->user->is('guest')) {
        		$this->jsObject['allowedSeals'] = $app->controller('seal')->apiQuery($query);

            	if($app->user->is('admin') || $app->user->is('superAdmin') || $this->jsObject['allowedSeals'] > 0) {
            		$this->jsObject['canRelateSeal'] = true;
            	} else {
            		$this->jsObject['canRelateSeal'] = false;
            	}
            }
        }

    function addProjectEventsToJs(Entities\Project $entity){
        $app = App::i();

        $ids = $entity->getChildrenIds();

        $ids[] = $entity->id;

        $in = implode(',', array_map(function ($e){ return '@Project:' . $e; }, $ids));

        $this->jsObject['entity']['events'] = $app->controller('Event')->apiQuery([
            '@select' => 'id,name,shortDescription,classificacaoEtaria,singleUrl,occurrences,terms,status,owner.id,owner.name,owner.singleUrl',
            'project' => 'IN(' . $in . ')',
            '@permissions' => 'view',
            '@files' => '(avatar.avatarSmall):url'
        ]);
    }

    function addProjectToJs(Entities\Project $entity){
        $app = App::i();

        $this->jsObject['entity']['useRegistrations'] = $entity->useRegistrations;
        $this->jsObject['entity']['registrationFileConfigurations'] = $entity->registrationFileConfigurations ? $entity->registrationFileConfigurations->toArray() : array();
        $this->jsObject['entity']['registrationFieldConfigurations'] = $entity->registrationFieldConfigurations ? $entity->registrationFieldConfigurations->toArray() : array();

        usort($this->jsObject['entity']['registrationFileConfigurations'], function($a,$b){
            if($a->title > $b->title){
                return 1;
            }else if($a->title < $b->title){

            }else{
                return 0;
            }
        });

        $field_types = array_values($app->getRegisteredRegistrationFieldTypes());


        usort($field_types, function ($a,$b){
            return strcmp($a->name, $b->name);
        });

        $this->jsObject['registrationFieldTypes'] = $field_types;

        $this->jsObject['entity']['registrationCategories'] = $entity->registrationCategories;
        $this->jsObject['entity']['published'] = $entity->publishedRegistrations;

        if($entity->canUser('@control')){
            $this->jsObject['entity']['registrations'] = $entity->allRegistrations ? $entity->allRegistrations : array();
        } else {
        $this->jsObject['entity']['registrations'] = $entity->sentRegistrations ? $entity->sentRegistrations : array();
        }
        $this->jsObject['entity']['registrationRulesFile'] = $entity->getFile('rules');
        $this->jsObject['entity']['canUserModifyRegistrationFields'] = $entity->canUser('modifyRegistrationFields');
        $this->jsObject['projectRegistrationsEnabled'] = App::i()->config['app.enableProjectRegistration'];
    }

    function addRegistrationToJs(Entities\Registration $entity){
        $this->jsObject['entity']['registrationFileConfigurations'] = $entity->project->registrationFileConfigurations ? $entity->project->registrationFileConfigurations->toArray() : array();
        usort($this->jsObject['entity']['registrationFileConfigurations'], function($a,$b){
            if($a->title > $b->title){
                return 1;
            }else if($a->title < $b->title){

            }else{
                return 0;
            }
        });
        $this->jsObject['entity']['registrationCategories'] = $entity->project->registrationCategories;
        $this->jsObject['entity']['registrationFiles'] = $entity->files;
        $this->jsObject['entity']['registrationAgents'] = array();
        if($entity->project->canUser('@control')){
            $this->jsObject['registration'] = $entity;
        }
        foreach($entity->_getDefinitionsWithAgents() as $def){
            $agent = $def->agent;
            if($agent){
                $def->agent = $agent->simplify('id,name,shortDescription,singleUrl');
                $def->agent->avatarUrl = $agent->avatar ? $agent->avatar->transform('avatarSmall')->url : null;
                if($entity->status > 0){ // is sent
                    if(isset($entity->agentsData[$def->agentRelationGroupName])){
                        foreach($entity->agentsData[$def->agentRelationGroupName] as $prop => $val){
                            $def->agent->$prop = $val;
                        }
                    }
                }
            }
            $this->jsObject['entity']['registrationAgents'][] = $def;
        }
    }

    /**
    * Returns a verified entity
    * @param type $entity_class
    * @return \MapasCulturais\Entity
    */
    function getOneVerifiedEntity($entity_class) {
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $entity_class;

        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $controller = $app->getControllerByEntity($entity_class);

        if ($entity_class === 'MapasCulturais\Entities\Event') {
            $entities = $controller->apiQueryByLocation(array(
                '@from' => date('Y-m-d'),
                '@to' => date('Y-m-d', time() + 28 * 24 * 3600),
                '@verified' => 'IN(1)',
                '@select' => 'id'
            ));

        }else{

            $entities = $controller->apiQuery([
                '@select' => 'id',
                '@verified' => 'IN(1)'
            ]);
        }

        $ids = array_map(function($item) {
            return $item['id'];
        }, $entities);

        if ($ids) {
            $id = $ids[array_rand($ids)];
            $result = $app->repo($entity_class)->find($id);
            $result->refresh();
        } else {
            $result = null;
        }

        $app->cache->save($cache_id, $result, 120);

        return $result;
    }

    function getEntityFeaturedImageUrl($entity) {
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $entity;

        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        if (key_exists('gallery', $entity->files)) {
            $result = $entity->files['gallery'][array_rand($entity->files['gallery'])]->transform('galleryFull')->url;
        } elseif (key_exists('avatar', $entity->files)) {
            $result = $entity->files['avatar']->transform('galleryFull')->url;
        } else {
            $result = null;
        }

        $app->cache->save($cache_id, $result, 1800);

        return $result;
    }

    function getNumEntities($class, $verified = 'all', $use_cache = true, $cache_lifetime = 300){
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $class . ':' . $verified;

        if($use_cache && $app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $controller = $app->getControllerByEntity($class);

        $q = ['@count'=>1];

        if($verified === true){
            $q['@verified'] = 'IN(1)';
        }

        $result = $controller->apiQuery($q);

        if($use_cache){
            $app->cache->save($cache_id, $result, $cache_lifetime);
        }

        return $result;
    }

    function getNumEvents($from = null, $to = null){
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $to . ':' . $from;

        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $result = $app->controller('Event')->apiQueryByLocation(array(
            '@count' => 1,
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', time() + 365 * 24 * 3600)
        ));

        $app->cache->save($cache_id, $result, 120);

        return $result;
    }

    function getNumVerifiedEvents($from = null, $to = null){
        $app = \MapasCulturais\App::i();

        $cache_id = __METHOD__ . ':' . $to . ':' . $from;

        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $result = $app->controller('Event')->apiQueryByLocation(array(
            '@count' => 1,
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', time() + 365 * 24 * 3600),
            '@verified' => 'IN(1)'
        ));

        $app->cache->save($cache_id, $result, 120);

        return $result;
    }

    function getRegistrationStatusName($registration){
        switch ($registration->status) {
            case \MapasCulturais\Entities\Registration::STATUS_APPROVED:
                return 'approved';
                break;
            case \MapasCulturais\Entities\Registration::STATUS_REJECTED:
                return 'rejected';
                break;
            case \MapasCulturais\Entities\Registration::STATUS_MAYBE:
                return 'maybe';
                break;
            case \MapasCulturais\Entities\Registration::STATUS_WAITING:
                return 'waiting';
                break;

        }
    }


    function registerMetadata($entity_class, $key, $cfg) {
        $app = \MapasCulturais\App::i();
        $def = new \MapasCulturais\Definitions\Metadata($key, $cfg);
        return $app->registerMetadata($def, $entity_class);

    }

    function registerEventMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Event', $key, $cfg);
    }

    function registerSpaceMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Space', $key, $cfg);
    }

    function registerAgentMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Agent', $key, $cfg);
    }

    function registerProjectMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Project', $key, $cfg);
    }

    function registerSealMetadata($key, $cfg) {
        return $this->registerMetadata('MapasCulturais\Entities\Seal', $key, $cfg);
    }



}
