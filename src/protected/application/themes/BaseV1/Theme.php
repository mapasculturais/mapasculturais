<?php

namespace MapasCulturais\Themes\BaseV1;

use MapasCulturais;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Notification;

class Theme extends MapasCulturais\Theme {

    protected $_libVersions = array(
        'leaflet' => '0.7.3',
        'angular' => '1.2.26',
        'jquery' => '2.1.1',
        'jquery-ui' => '1.11.1',
        'select2' => '3.5.0',
        'magnific-popup' => '0.9.9',
        'x-editable' => 'jquery-editable-dev-1.5.2'
    );

    static function getThemeFolder() {
        return __DIR__;
    }

    protected static function _getTexts(){
        return array(
            'site: name' => App::i()->config['app.siteName'],
            'site: description' => App::i()->config['app.siteDescription'],
            'site: in the region' => 'na região',
            'site: of the region' => 'da região',
            'site: owner' => 'Secretaria',
            'site: by the site owner' => 'pela Secretaria',

            'home: title' => "Bem-vind@!",
            'home: abbreviation' => "MC",
            'home: colabore' => "Colabore com o Mapas Culturais",
            'home: welcome' => "O Mapas Culturais é uma plataforma livre, gratuita e colaborativa de mapeamento cultural.",
            'home: events' => "Você pode pesquisar eventos culturais nos campos de busca combinada. Como usuário cadastrado, você pode incluir seus eventos na plataforma e divulgá-los gratuitamente.",
            'home: agents' => "Você pode colaborar na gestão da cultura com suas próprias informações, preenchendo seu perfil de agente cultural. Neste espaço, estão registrados artistas, gestores e produtores; uma rede de atores envolvidos na cena cultural da região. Você pode cadastrar um ou mais agentes (grupos, coletivos, bandas instituições, empresas, etc.), além de associar ao seu perfil eventos e espaços culturais com divulgação gratuita.",
            'home: spaces' => "Procure por espaços culturais incluídos na plataforma, acessando os campos de busca combinada que ajudam na precisão de sua pesquisa. Cadastre também os espaços onde desenvolve suas atividades artísticas e culturais.",
            'home: projects' => "Reúne projetos culturais ou agrupa eventos de todos os tipos. Neste espaço, você encontra leis de fomento, mostras, convocatórias e editais criados, além de diversas iniciativas cadastradas pelos usuários da plataforma. Cadastre-se e divulgue seus projetos.",
            'home: home_devs' => 'Existem algumas maneiras de desenvolvedores interagirem com o Mapas Culturais. A primeira é através da nossa <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Com ela você pode acessar os dados públicos no nosso banco de dados e utilizá-los para desenvolver aplicações externas. Além disso, o Mapas Culturais é construído a partir do sofware livre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturais</a>, criado em parceria com o <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>, e você pode contribuir para o seu desenvolvimento através do <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.',

            'search: verified results' => 'Resultados Verificados',
            'search: verified' => "Verificados"
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

    protected function _init() {
        $app = App::i();


        /* === NOTIFICATIONS  === */
        // para todos os requests
        $app->hook('workflow(<<*>>).create', function() use($app) {

            if ($this->notifications) {
                $app->disableAccessControl();
                foreach ($this->notifications as $n) {
                    $n->delete();
                }
                $app->enableAccessControl();
            }

            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;
            $destination = $this->destination;

            $origin_type = strtolower($origin->entityType);
            $origin_url = $origin->singleUrl;
            $origin_name = $origin->name;

            $destination_url = $destination->singleUrl;
            $destination_name = $destination->name;

            $profile_link = "<a href=\"{$profile->singleUrl}\">{$profile->name}</a>";
            $destination_link = "<a href=\"{$destination_url}\">{$destination_name}</a>";
            $origin_link = "<a href=\"{$origin_url}\">{$origin_name}</a>";

            switch ($this->getClassName()) {
                case "MapasCulturais\Entities\RequestAgentRelation":
                    if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                        $message = "{$profile_link} quer relacionar o agente {$destination_link} à inscrição {$origin->number} no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        $message_to_requester = "Sua requisição para relacionar o agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a> foi enviada.";
                    }else{
                        $message = "{$profile_link} quer relacionar o agente {$destination_link} ao {$origin_type} {$origin_link}.";
                        $message_to_requester = "Sua requisição para relacionar o agente {$destination_link} ao {$origin_type} {$origin_link} foi enviada.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = "{$profile_link} está requisitando a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    $message_to_requester = "Sua requisição para alterar a propriedade do {$origin_type} {$origin_link} para o agente {$destination_link} foi enviada.";
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = "{$profile_link} quer que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}.";
                    ;
                    $message_to_requester = "Sua requisição para fazer do {$origin_type} {$origin_link} um {$origin_type} filho de {$destination_link} foi enviada.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = "{$profile_link} quer adicionar o evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}.";
                    $message_to_requester = "Sua requisição para criar a ocorrência do evento {$origin_link} no espaço {$destination_link} foi enviada.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = "{$profile_link} quer relacionar o evento {$origin_link} ao projeto {$destination_link}.";
                    $message_to_requester = "Sua requisição para associar o evento {$origin_link} ao projeto {$destination_link} foi enviada.";
                    break;
                default:
                    $message = $message_to_requester = "REQUISIÇÃO - NÃO DEVE ENTRAR AQUI";
                    break;
            }

            // message to requester user
            $notification = new Notification;
            $notification->user = $requester;
            $notification->message = $message_to_requester;
            $notification->request = $this;
            $notification->save(true);

            $notified_user_ids = array($requester->id);


            foreach ($destination->usersWithControl as $user) {
                // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
                if (in_array($user->id, $notified_user_ids))
                    continue;

                $notified_user_ids[] = $user->id;

                $notification = new Notification;
                $notification->user = $user;
                $notification->message = $message;
                $notification->request = $this;
                $notification->save(true);
            }

            if (!$requester->equals($origin->ownerUser) && !in_array($origin->ownerUser->id, $notified_user_ids)) {
                $notification = new Notification;
                $notification->user = $origin->ownerUser;
                $notification->message = $message;
                $notification->request = $this;
                $notification->save(true);
            }
        });

        $app->hook('workflow(<<*>>).approve:before', function() use($app) {
            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;
            $destination = $this->destination;

            $origin_type = strtolower($origin->entityType);
            $origin_url = $origin->singleUrl;
            $origin_name = $origin->name;

            $destination_url = $destination->singleUrl;
            $destination_name = $destination->name;

            $profile_link = "<a href=\"{$profile->singleUrl}\">{$profile->name}</a>";
            $destination_link = "<a href=\"{$destination_url}\">{$destination_name}</a>";
            $origin_link = "<a href=\"{$origin_url}\">{$origin_name}</a>";

            switch ($this->getClassName()) {
                case "MapasCulturais\Entities\RequestAgentRelation":
                    if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                        $message = "{$profile_link} aceitou o relacionamento do agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                    }else{
                        $message = "{$profile_link} aceitou o relacionamento do agente {$destination_link} com o {$origin_type} {$origin_link}.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = "{$profile_link} aceitou a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = "{$profile_link} aceitou que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = "{$profile_link} aceitou adicionar o evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = "{$profile_link} aceitou relacionar o evento {$origin_link} ao projeto {$destination_link}.";
                    break;
                default:
                    $message = "A requisição foi aprovada.";
                    break;
            }

            $users = array();

            // notifica quem fez a requisição
            $users[] = $this->requesterUser;

            if ($this->getClassName() === "MapasCulturais\Entities\RequestChangeOwnership" && $this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                // se não foi o dono da entidade de destino que fez a requisição, notifica o dono
                if (!$destination->ownerUser->equals($this->requesterUser))
                    $users[] = $destination->ownerUser;

                // se não é o dono da entidade de origem que está aprovando, notifica o dono
                if (!$origin->ownerUser->equals($app->user))
                    $users[] = $origin->ownerUser;
            }else {
                // se não foi o dono da entidade de origem que fez a requisição, notifica o dono
                if (!$origin->ownerUser->equals($this->requesterUser))
                    $users[] = $origin->ownerUser;

                // se não é o dono da entidade de destino que está aprovando, notifica o dono
                if (!$destination->ownerUser->equals($app->user))
                    $users[] = $destination->ownerUser;
            }

            $notified_user_ids = array();

            foreach ($users as $u) {
                // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
                if (in_array($u->id, $notified_user_ids))
                    continue;

                $notified_user_ids[] = $u->id;

                $notification = new Notification;
                $notification->message = $message;
                $notification->user = $u;
                $notification->save(true);
            }
        });


        $app->hook('workflow(<<*>>).reject:before', function() use($app) {
            $requester = $app->user;
            $profile = $requester->profile;

            $origin = $this->origin;
            $destination = $this->destination;

            $origin_type = strtolower($origin->entityType);
            $origin_url = $origin->singleUrl;
            $origin_name = $origin->name;

            $destination_url = $destination->singleUrl;
            $destination_name = $destination->name;

            $profile_link = "<a href=\"{$profile->singleUrl}\">{$profile->name}</a>";
            $destination_link = "<a href=\"{$destination_url}\">{$destination_name}</a>";
            $origin_link = "<a href=\"{$origin_url}\">{$origin_name}</a>";

            switch ($this->getClassName()) {
                case "MapasCulturais\Entities\RequestAgentRelation":
                    if($origin->canUser('@control')){
                        if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                            $message = "{$profile_link} cancelou o relacionamento do agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        }else{
                            $message = "{$profile_link} cancelou o pedido de relacionamento do agente {$destination_link} com o {$origin_type} {$origin_link}.";
                        }
                    }else{
                        if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                            $message = "{$profile_link} rejeitou o relacionamento do agente {$destination_link} à inscrição <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> no projeto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        }else{
                            $message = "{$profile_link} rejeitou o relacionamento do agente {$destination_link} com o {$origin_type} {$origin_link}.";
                        }
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    if ($this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                        $message = $this->requesterUser->equals($requester) ?
                                "{$profile_link} cancelou o pedido de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}." :
                                "{$profile_link} rejeitou a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    } else {
                        $message = $this->requesterUser->equals($requester) ?
                                "{$profile_link} cancelou o pedido de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}." :
                                "{$profile_link} rejeitou a mudança de propriedade do {$origin_type} {$origin_link} para o agente {$destination_link}.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelou o pedido para que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}." :
                            "{$profile_link} rejeitou que o {$origin_type} {$origin_link} seja um {$origin_type} filho de {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelou o pedido de autorização do evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}." :
                            "{$profile_link} rejeitou o evento {$origin_link} que ocorre <em>{$this->rule->description}</em> no espaço {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelou o pedido de relacionamento do evento {$origin_link} ao projeto {$destination_link}." :
                            "{$profile_link} rejeitou o relacionamento do evento {$origin_link} ao projeto {$destination_link}.";
                    break;
                default:
                    $message = $origin->canUser('@control') ?
                            "A requisição foi cancelada." :
                            "A requisição foi rejeitada.";
                    break;
            }

            $users = array();

            if (!$app->user->equals($this->requesterUser)) {
                // notifica quem fez a requisição
                $users[] = $this->requesterUser;
            }

            if ($this->getClassName() === "MapasCulturais\Entities\RequestChangeOwnership" && $this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                // se não foi o dono da entidade de destino que fez a requisição, notifica o dono
                if (!$destination->ownerUser->equals($this->requesterUser))
                    $users[] = $destination->ownerUser;

                // se não é o dono da entidade de origem que está rejeitando, notifica o dono
                if (!$origin->ownerUser->equals($app->user))
                    $users[] = $origin->ownerUser;
            }else {
                // se não foi o dono da entidade de origem que fez a requisição, notifica o dono
                if (!$origin->ownerUser->equals($this->requesterUser))
                    $users[] = $origin->ownerUser;

                // se não é o dono da entidade de destino que está rejeitando, notifica o dono
                if (!$destination->ownerUser->equals($app->user))
                    $users[] = $destination->ownerUser;
            }

            $notified_user_ids = array();

            foreach ($users as $u) {
                // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
                if (in_array($u->id, $notified_user_ids))
                    continue;

                $notified_user_ids[] = $u->id;

                $notification = new Notification;
                $notification->message = $message;
                $notification->user = $u;
                $notification->save(true);
            }
        });


        /* ---------------------- */

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

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->assetManager->publishAsset('css/main.css.map', 'css/main.css.map');

            $this->jsObject['assets'] = array();
            $this->jsObject['templateUrl'] = array();
            $this->jsObject['spinnerUrl'] = $this->asset('img/spinner.gif', false);

            $this->jsObject['assets']['fundo'] = $this->asset('img/fundo.png', false);
            $this->jsObject['assets']['instituto-tim'] = $this->asset('img/instituto-tim-white.png', false);
            $this->jsObject['assets']['verifiedIcon'] = $this->asset('img/verified-icon.png', false);
            $this->jsObject['assets']['avatarAgent'] = $this->asset('img/avatar--agent.png', false);
            $this->jsObject['assets']['avatarSpace'] = $this->asset('img/avatar--space.png', false);
            $this->jsObject['assets']['avatarEvent'] = $this->asset('img/avatar--event.png', false);
            $this->jsObject['assets']['avatarProject'] = $this->asset('img/avatar--project.png', false);

            $this->jsObject['isEditable'] = $this->isEditable();
            $this->jsObject['isSearch'] = $this->isSearch();

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

            $this->jsObject['mapMaxClusterRadius'] = $app->config['maps.maxClusterRadius'];
            $this->jsObject['mapSpiderfyDistanceMultiplier'] = $app->config['maps.spiderfyDistanceMultiplier'];
            $this->jsObject['mapMaxClusterElements'] = $app->config['maps.maxClusterElements'];

            $this->jsObject['mapGeometryFieldQuery'] = $app->config['maps.geometryFieldQuery'];

            $this->jsObject['labels'] = array(
                'agent' => \MapasCulturais\Entities\Agent::getPropertiesLabels(),
                'project' => \MapasCulturais\Entities\Project::getPropertiesLabels(),
                'event' => \MapasCulturais\Entities\Event::getPropertiesLabels(),
                'space' => \MapasCulturais\Entities\Space::getPropertiesLabels(),
                'registration' => \MapasCulturais\Entities\Registration::getPropertiesLabels(),
            );

            $this->jsObject['routes'] = $app->config['routes'];

            $this->addDocumentMetas();
            $this->includeVendorAssets();
            $this->includeCommonAssets();
            $this->_populateJsObject();
        });

        $app->hook('view.render(<<agent|space|project|event>>/<<single|edit|create>>):before', function() {
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

        // sempre que insere uma imagem cria o avatarSmall
        $app->hook('entity(<<agent|space|event|project>>).file(avatar).insert:after', function() {
            $this->transform('avatarSmall');
            $this->transform('avatarBig');
        });

        $app->hook('entity(<<agent|space|event|project>>).file(header).insert:after', function() {
            $this->transform('header');
        });

        $app->hook('entity(<<agent|space|event|project>>).file(gallery).insert:after', function() {
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
    }

    function register() {
        $app = App::i();
        foreach ($app->config['app.geoDivisionsHierarchy'] as $slug => $name) {
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
        //$this->printStyles('fonts');
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

        $site_name = $app->siteName;
        $title = $app->view->getTitle($entity);
        $image_url = $app->view->asset('img/share.png', false);
        if ($entity) {
            $description = $entity->shortDescription ? $entity->shortDescription : $title;
            if ($entity->avatar)
                $image_url = $entity->avatar->transform('avatarBig')->url;
        }else {
            $description = $app->siteDescription;
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
//        $this->enqueueStyle('vendor', 'x-editable-tip', "vendor/x-editable-{$versions['x-editable']}/css/tip-yellowsimple.css", array('x-editable'));

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
        //$app->enqueueStyle( 'vendor', 'leaflet-label',           'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-label/leaflet.label.css',       array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-label', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.label-master/dist/leaflet.label-src.js', array('leaflet'));

        //Leaflet Draw
        $this->enqueueStyle('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw.css', array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw-src.js', array('leaflet'));

        $this->enqueueScript('vendor', 'google-maps-api', 'http://maps.google.com/maps/api/js?v=3.2&sensor=false');

        //Leaflet Plugins (Google)false');
        $this->enqueueScript('vendor', 'leaflet-google-tile', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-plugins-master/layer/tile/Google.js', array('leaflet'));

        $this->enqueueStyle('vendor', 'magnific-popup', "vendor/Magnific-Popup-{$versions['magnific-popup']}/magnific-popup.css");
        $this->enqueueScript('vendor', 'magnific-popup', "vendor/Magnific-Popup-{$versions['magnific-popup']}/jquery.magnific-popup.js", array('jquery'));

        $this->enqueueScript('vendor', 'momentjs', 'vendor/moment.js');
        $this->enqueueScript('vendor', 'momentjs-pt-br', 'vendor/moment.pt-br.js', array('momentjs'));

        $this->enqueueScript('vendor', 'jquery-ui-core', "vendor/jquery-ui-{$versions['jquery-ui']}/core.js", array('jquery'));
        $this->enqueueScript('vendor', 'jquery-ui-position', "vendor/jquery-ui-{$versions['jquery-ui']}/position.js", array('jquery-ui-core'));
        $this->enqueueScript('vendor', 'jquery-ui-datepicker', "vendor/jquery-ui-{$versions['jquery-ui']}/datepicker.js", array('jquery-ui-core'));
        $this->enqueueScript('vendor', 'jquery-ui-datepicker-pt-BR', "vendor/jquery-ui-{$versions['jquery-ui']}/datepicker-pt-BR.js", array('jquery-ui-datepicker'));

        $this->enqueueScript('vendor', 'angular', "vendor/angular-{$versions['angular']}/angular.js", array('jquery', 'jquery-ui-datepicker-pt-BR', 'jquery-ui-position'));
        $this->enqueueScript('vendor', 'angular-sanitize', "vendor/angular-{$versions['angular']}/angular-sanitize.js", array('angular'));

        $this->enqueueScript('vendor', 'angular-rison', '/vendor/angular-rison.js', array('angular'));
        $this->enqueueScript('vendor', 'ng-infinite-scroll', '/vendor/ng-infinite-scroll/ng-infinite-scroll.js', array('angular'));

        $this->enqueueScript('vendor', 'angular-ui-date', '/vendor/ui-date-master/src/date.js', array('jquery-ui-datepicker-pt-BR', 'angular'));

    }

    function includeCommonAssets() {
        $this->getAssetManager()->publishFolder('fonts/');

        //$this->enqueueStyle('fonts', 'elegant', 'css/fonts.css');

        $this->enqueueStyle('app', 'main', 'css/main.css');

        $this->enqueueScript('app', 'tim', 'js/tim.js');
        $this->enqueueScript('app', 'mapasculturais', 'js/mapasculturais.js', array('tim'));

        $this->enqueueScript('app', 'ng-mapasculturais', 'js/ng-mapasculturais.js');
        $this->enqueueScript('app', 'notifications', 'js/Notifications.js', array('ng-mapasculturais'));



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

        $this->enqueueScript('app', 'SearchService', 'js/SearchService.js', array('ng-mapasculturais', 'SearchSpatial'));
        $this->enqueueScript('app', 'FindOneService', 'js/FindOneService.js', array('ng-mapasculturais', 'SearchSpatial'));
        $this->enqueueScript('app', 'SearchMapController', 'js/SearchMap.js', array('ng-mapasculturais', 'map'));
        $this->enqueueScript('app', 'SearchSpatial', 'js/SearchSpatial.js', array('ng-mapasculturais', 'map'));

        $this->enqueueScript('app', 'Search', 'js/Search.js', array('ng-mapasculturais', 'SearchSpatial', 'SearchMapController', 'FindOneService', 'SearchService'));
    }

    function includeMapAssets() {
        $app = App::i();

        $this->assetManager->publishAsset('css/main.css.map', 'css/main.css.map');

        $this->jsObject['assets']['avatarAgent'] = $this->asset('img/avatar--agent.png', false);
        $this->jsObject['assets']['avatarSpace'] = $this->asset('img/avatar--space.png', false);
        $this->jsObject['assets']['avatarEvent'] = $this->asset('img/avatar--event.png', false);
        $this->jsObject['assets']['avatarProject'] = $this->asset('img/avatar--project.png', false);


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

        $this->jsObject['assets']['pinAgentEventGroup'] = $this->asset('img/agrupador-combinado-agente-evento.png', false);
        $this->jsObject['assets']['pinSpaceEventGroup'] = $this->asset('img/agrupador-combinado-espaco-evento.png', false);
        $this->jsObject['assets']['pinAgentSpaceGroup'] = $this->asset('img/agrupador-combinado-espaco-agente.png', false);

        $this->jsObject['assets']['pinAgentSpaceEventGroup'] = $this->asset('img/agrupador-combinado.png', false);

        $this->jsObject['geoDivisionsHierarchy'] = $app->config['app.geoDivisionsHierarchy'];

        $this->enqueueScript('app', 'map', 'js/map.js');
    }

    function includeAngularEntityAssets($entity) {
        $this->jsObject['templateUrl']['editBox'] = $this->asset('js/directives/edit-box.html', false);
        $this->jsObject['templateUrl']['findEntity'] = $this->asset('js/directives/find-entity.html', false);
        $this->jsObject['templateUrl']['MCSelect'] = $this->asset('js/directives/mc-select.html', false);

        $this->enqueueScript('app', 'change-owner', 'js/ChangeOwner.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity', 'js/Entity.js', array('mapasculturais', 'ng-mapasculturais', 'change-owner'));
        $this->enqueueScript('app', 'ng-project', 'js/Project.js', array('entity'));
        $this->enqueueScript('app', 'related-agents', 'js/RelatedAgents.js', array('ng-mapasculturais'));
        if(!isset($this->jsObject['entity'])){
            $this->jsObject['entity'] = array();
        }

        $this->jsObject['entity']['id'] = $entity->id;

        $roles = [];
        if(!\MapasCulturais\App::i()->user->is('guest')){
            foreach(\MapasCulturais\App::i()->user->roles as $r){
                $roles[] = $r->name;
            }
        }

        $this->jsObject['roles'] = $roles;
        $this->jsObject['request']['id'] = $entity->id;

        $this->jsObject['entity'] = array_merge($this->jsObject['entity'], array(
            'ownerId' => $entity->owner->id, // ? $entity->owner->id : null,
            'ownerUserId' => $entity->ownerUser->id,
            'definition' => $entity->getPropertiesMetadata(),
            'userHasControl' => $entity->canUser('@control'),
            'canUserCreateRelatedAgentsWithControl' => $entity->canUser('createAgentRelationWithControl'),
        ));
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
            'once' => 'uma vez',
            'daily' => 'todos os dias',
            'weekly' => 'semanal',
            'monthly' => 'mensal',
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
                'user' => 'EQ(@me)'
            ));
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

    function addProjectToJs(Entities\Project $entity){
        $this->jsObject['entity']['useRegistrations'] = $entity->useRegistrations;
        $this->jsObject['entity']['registrationFileConfigurations'] = $entity->registrationFileConfigurations ? $entity->registrationFileConfigurations->toArray() : array();
        usort($this->jsObject['entity']['registrationFileConfigurations'], function($a,$b){
            if($a->title > $b->title){
                return 1;
            }else if($a->title < $b->title){

            }else{
                return 0;
            }
        });
        $this->jsObject['entity']['registrationCategories'] = $entity->registrationCategories;
        $this->jsObject['entity']['published'] = $entity->publishedRegistrations;
        $this->jsObject['entity']['registrations'] = $entity->sentRegistrations ? $entity->sentRegistrations : array();
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

        $dql = "
        SELECT
           e.id
        FROM
           $entity_class e
        WHERE
           e.status > 0 AND
           e.isVerified = TRUE
       ";

        if ($entity_class === 'MapasCulturais\Entities\Event') {
            $events = $app->controller('Event')->apiQueryByLocation(array(
                '@from' => date('Y-m-d'),
                '@to' => date('Y-m-d', time() + 28 * 24 * 3600),
                'isVerified' => 'EQ(true)',
                '@select' => 'id'
            ));
            $event_ids = array_map(function($item) {
                return $item['id'];
            }, $events);

            if ($event_ids)
                $dql .= ' AND e.id IN (' . implode(',', $event_ids) . ')';
            else
                return null;
        }

        $ids = $app->em->createQuery($dql)
                ->useQueryCache(true)
                ->setResultCacheLifetime(60 * 5)
                ->getScalarResult();

        if ($ids) {
            $id = $ids[array_rand($ids)]['id'];
            return $app->repo($entity_class)->find($id);
        } else {
            return null;
        }
    }

    function getEntityFeaturedImageUrl($entity) {
        if (key_exists('gallery', $entity->files)) {
            return $entity->files['gallery'][array_rand($entity->files['gallery'])]->transform('galleryFull')->url;
        } elseif (key_exists('avatar', $entity->files)) {
            return $entity->files['avatar']->transform('galleryFull')->url;
        } else {
            return null;
        }
    }

    function getNumEntities($class, $verified = 'all', $use_cache = true, $cache_lifetime = 300){
        $em = App::i()->em;
        $dql = "SELECT COUNT(e) FROM $class e WHERE e.status > 0";

        if(is_bool($verified)){
            if($verified){
                $dql .= ' AND e.isVerified = TRUE';
            }else{
                $dql .= ' AND e.isVerified = FALSE';
            }
        }

        $q = $em->createQuery($dql);

        if($use_cache){
            $q->useQueryCache(true)->setResultCacheLifetime($cache_lifetime);
        }

        return $q->getSingleScalarResult();
    }

    function getNumEvents($from = null, $to = null){
        return App::i()->controller('Event')->apiQueryByLocation(array(
            '@count' => 1,
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', time() + 365 * 24 * 3600)
        ));
    }

    function getNumVerifiedEvents($from = null, $to = null){
        return App::i()->controller('Event')->apiQueryByLocation(array(
            '@count' => 1,
            '@from' => date('Y-m-d'),
            '@to' => date('Y-m-d', time() + 365 * 24 * 3600),
            'isVerified' => 'EQ(true)'
        ));
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
}
