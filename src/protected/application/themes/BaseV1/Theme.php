<?php

namespace MapasCulturais\Themes\BaseV1;

use MapasCulturais;
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Notification;

class Theme extends MapasCulturais\Theme {

    protected $_libVersions = array(
        'leaflet' => '0.7.3',
        'angular' => '1.5.5',
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
            'site: in the region' => 'en la región',
            'site: of the region' => 'de la región',
            'site: owner' => 'Secretaría',
            'site: by the site owner' => 'por la Secretaría',
            
            'home: title' => "¡Bienvenidos!",
            'home: abbreviation' => "MC",
            'home: colabore' => "Colabore con Mapas Culturales",
            'home: welcome' => "Mapas Culturales es una plataforma de software libre, de cartografía cultural libre y colaborativa.",
            'home: events' => "Puede buscar eventos culturales en los campos de búsqueda combinados. Como usuario registrado, puede incluir su evento en la plataforma y darlo a conocer de forma gratuita.",
            'home: agents' => "Puede colaborar en la gestión de la cultura con su propia información completando su perfil de agente cultural. En este espacio, son artistas registrados, gestores y productores; una red de actores involucrados en la escena cultural de la región. Usted puede registrar uno o más agentes (grupos, colectivos, grupos de instituciones, empresas, etc.), y asociar con sus eventos de perfil y espacios culturales con libre difusión.",
            'home: spaces' => "Busque espacios culturales incluidos en la plataforma, con el acceso a los campos de búsqueda combinados que ayudan a la exactitud de su búsqueda. Firmar los espacios donde se desarrollan sus actividades artísticas y culturales.",
            'home: projects' => "Reúne a proyectos culturales o eventos grupales de todo tipo. En este espacio, se encuentran las formas de financiación, espectáculos, reuniones y edictos creados, y varias iniciativas registradas por los usuarios de la plataforma. Regístrese ahora y publicar sus proyectos.",
            'home: home_devs' => 'Hay varias formas en las que los desarrolladores interactúan con los mapas culturales. La primera es a través de nuestra <a href="https://github.com/hacklabr/mapasculturais/blob/master/doc/api.md" target="_blank">API</a>. Con ella usted puede acceder a los datos públicos de nuestra base de datos y utilizarlos para desarrollar aplicaciones externas. Además, los Mapas Culturales está realizado a partir de sofware libre <a href="http://institutotim.org.br/project/mapas-culturais/" target="_blank">Mapas Culturales</a>, creado en asociación con el <a href="http://institutotim.org.br" target="_blank">Instituto TIM</a>,  y puede contribuir con su desarrollo a través de <a href="https://github.com/hacklabr/mapasculturais/" target="_blank">GitHub</a>.',

            'search: verified results' => 'Resultados Verificados',
            'search: verified' => "Verificados",
            
            
            'entities: Spaces of the agent'=> 'Espacios del agente',
            'entities: Space Description'=> 'Descripción del Espacio',
            'entities: My Spaces'=> 'Mis Espacios',
            'entities: My spaces'=> 'Mis Espacios',
            
            'entities: no registered spaces'=> 'ningún espacio registrado',
            'entities: no spaces'=> 'ningún espacio',
            
            'entities: Space' => 'Espacio',
            'entities: Spaces' => 'Espacios',
            'entities: space' => 'espacio',
            'entities: spaces' => 'espacios',
            'entities: parent space' => 'espacio padre',
            'entities: a space' => 'un espacio',
            'entities: the space' => 'el espacio',
            'entities: of the space' => 'del espacio',            
            'entities: In this space' => 'En este espacio',
            'entities: in this space' => 'en este espacio',
            'entities: registered spaces' => 'espacios registrados',
            'entities: new space' => 'nuevo espacio',
            'entities: space found' => 'espacio encontrado',
            'entities: spaces found' => 'espacios encontrados',
            'entities: event found' => 'evento encontrado',
            'entities: events found' => 'eventos encontrados',
            'entities: agent found' => 'agente encontrado',
            'entities: agents found' => 'agentes encontrados',
            'entities: project found' => 'proyecto encontrado',
            'entities: project found' => 'proyectos encontrados'
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
                        $message = "{$profile_link} quiere relacionar el agente {$destination_link} a la inscripción {$origin->number} del proyecto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        $message_to_requester = "Su solicitud para relacionar el agente {$destination_link} a la inscripción <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> del proyecto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a> fue enviada.";
                    }else{
                        $message = "{$profile_link} quiere relacionar el agente {$destination_link} al {$origin_type} {$origin_link}.";
                        $message_to_requester = "Su solicitud para relacionar el agente {$destination_link} ao {$origin_type} {$origin_link} fue enviada.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = "{$profile_link} está solicitando el cambio de propiedad de {$origin_type} {$origin_link} para el agente {$destination_link}.";
                    $message_to_requester = "Su solicitud para cambiar la propiedad del {$origin_type} {$origin_link} para el agente {$destination_link} fue enviada.";
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = "{$profile_link} quiere que {$origin_type} {$origin_link} sea un {$origin_type} hijo de {$destination_link}.";
                    ;
                    $message_to_requester = "Su solicitud para hacer de {$origin_type} {$origin_link} un {$origin_type} hijo de {$destination_link} fue enviada.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = "{$profile_link} quiere agregar el evento {$origin_link} que ocorre <em>{$this->rule->description}</em> en el espacio {$destination_link}.";
                    $message_to_requester = "Su solicitud para crear la ocurrencia del evento {$origin_link} en el espacio {$destination_link} fue enviada.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = "{$profile_link} quiere relacionar el evento {$origin_link} al proyecto {$destination_link}.";
                    $message_to_requester = "Su solicitud para asociar el evento {$origin_link} al proyecto {$destination_link} fue enviada.";
                    break;
                default:
                    $message = $message_to_requester = "SOLICITUD - NO DEBE ENTRAR AQUÍ";
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
                // impede que a notificação seja entregue mais de una vez ao mesmo usuário se as regras acima se somarem
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
                        $message = "{$profile_link} relación aceptada del agente {$destination_link} a la inscripción <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> en el proyecto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                    }else{
                        $message = "{$profile_link} relación aceptada del agente {$destination_link} con el {$origin_type} {$origin_link}.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    $message = "{$profile_link} aceptado el cambio de propiedad de {$origin_type} {$origin_link} para el agente {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = "{$profile_link} aceptado que el {$origin_type} {$origin_link} sea un {$origin_type} hijo de {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = "{$profile_link} aceptado agregar el evento {$origin_link} que ocorre <em>{$this->rule->description}</em> en el espacio {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = "{$profile_link} aceptado relacionar el evento {$origin_link} al proyecto {$destination_link}.";
                    break;
                default:
                    $message = "La solicitud fue aprobada.";
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
                // impede que a notificação seja entregue mais de una vez ao mesmo usuário se as regras acima se somarem
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
                            $message = "{$profile_link} ralación cancelada del agente {$destination_link} a la inscripción <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> en el proyecto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        }else{
                            $message = "{$profile_link} cancelado el pedido de relacionar el agente {$destination_link} con el {$origin_type} {$origin_link}.";
                        }
                    }else{
                        if($origin->getClassName() === 'MapasCulturais\Entities\Registration'){
                            $message = "{$profile_link} rechazada la relación del agente {$destination_link} a la inscripción <a href=\"{$origin->singleUrl}\" >{$origin->number}</a> en el proyecto <a href=\"{$origin->project->singleUrl}\">{$origin->project->name}</a>.";
                        }else{
                            $message = "{$profile_link} rechazada la relación del agente {$destination_link} con el {$origin_type} {$origin_link}.";
                        }
                    }
                    break;
                case "MapasCulturais\Entities\RequestChangeOwnership":
                    if ($this->type === Entities\RequestChangeOwnership::TYPE_REQUEST) {
                        $message = $this->requesterUser->equals($requester) ?
                                "{$profile_link} cancelado el pedido de propiedad del {$origin_type} {$origin_link} para el agente {$destination_link}." :
                                "{$profile_link} rechazado el cambio de propiedad del {$origin_type} {$origin_link} para el agente {$destination_link}.";
                    } else {
                        $message = $this->requesterUser->equals($requester) ?
                                "{$profile_link} cancelado el pedido de propiedad del {$origin_type} {$origin_link} para el agente {$destination_link}." :
                                "{$profile_link} rechazado el cambio de propiedad del {$origin_type} {$origin_link} para el agente {$destination_link}.";
                    }
                    break;
                case "MapasCulturais\Entities\RequestChildEntity":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelado el pedido para que el {$origin_type} {$origin_link} sea un {$origin_type} hijo de {$destination_link}." :
                            "{$profile_link} rechazado que el {$origin_type} {$origin_link} sea un {$origin_type} hijo de {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventOccurrence":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelado el pedido de autorización del evento {$origin_link} que ocorre <em>{$this->rule->description}</em> en el espacio {$destination_link}." :
                            "{$profile_link} rechazado el evento {$origin_link} que ocorre <em>{$this->rule->description}</em> en el espacio {$destination_link}.";
                    break;
                case "MapasCulturais\Entities\RequestEventProject":
                    $message = $origin->canUser('@control') ?
                            "{$profile_link} cancelado el pedido de relacionar el evento {$origin_link} al proyecto {$destination_link}." :
                            "{$profile_link} rechazada la relación del evento {$origin_link} al proyecto {$destination_link}.";
                    break;
                default:
                    $message = $origin->canUser('@control') ?
                            "La solicitud fue cancelada." :
                            "La solicitud fue rechazada.";
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
                // impede que a notificação seja entregue mais de una vez ao mesmo usuário se as regras acima se somarem
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
                      js.src = "//connect.facebook.net/es_ES/all.js#xfbml=1";
                      fjs.parentNode.insertBefore(js, fjs);
                    }(document, 'script', 'facebook-jssdk'));</script>
                <!--fim do facebook-->
                <?php
            endif;
        });
        
        $this->jsObject['infoboxFields'] = 'id,singleUrl,name,subTitle,type,shortDescription,terms,project.name,project.singleUrl';
        
        $this->jsObject['EntitiesDescription'] = [
        		"agent" => \MapasCulturais\Entities\Agent::getPropertiesMetadata(),
        		"event" => \MapasCulturais\Entities\Event::getPropertiesMetadata(),
        		"space" => \MapasCulturais\Entities\Space::getPropertiesMetadata(),
        		"project" => \MapasCulturais\Entities\Project::getPropertiesMetadata()
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
            $this->jsObject['assets']['avatarSpace'] = $this->asset('img/avatar--space.png', false);
            $this->jsObject['assets']['avatarEvent'] = $this->asset('img/avatar--event.png', false);
            $this->jsObject['assets']['avatarProject'] = $this->asset('img/avatar--project.png', false);

            $this->jsObject['isEditable'] = $this->isEditable();
            $this->jsObject['isSearch'] = $this->isSearch();
            
            $this->jsObject['angularAppDependencies'] = [
                'entity.module.relatedAgents', 
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

        // Google Maps API only needed in site/search and space, agent and event singles
        if(preg_match('#site|space|agent|event#',    $this->controller->id) && preg_match('#search|single|edit|create#', $this->controller->action)){
            $this->enqueueScript('vendor', 'google-maps-api', 'http://maps.google.com/maps/api/js?v=3.2&sensor=false');
        }

        //Leaflet Plugins
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

        $this->enqueueScript('app', 'ng-mapasculturais', 'js/ng-mapasculturais.js', array('mapasculturais'));
        $this->enqueueScript('app', 'mc.module.notifications', 'js/ng.mc.module.notifications.js', array('ng-mapasculturais'));



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
        $this->enqueueScript('app', 'search.controller.spatial', 'js/ng.search.controller.spatial.js', array('ng-mapasculturais', 'map'));

        $this->enqueueScript('app', 'search.app', 'js/ng.search.app.js', array('ng-mapasculturais', 'search.controller.spatial', 'search.controller.map', 'search.service.findOne', 'search.service.find'));
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
        $this->enqueueScript('app', 'entity.module.project', 'js/ng.entity.module.project.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity.module.relatedAgents', 'js/ng.entity.module.relatedAgents.js', array('ng-mapasculturais'));
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

    function ajaxUploader($file_owner, $group_name, $response_action, $response_target, $response_template = '', $response_transform = '', $add_description_input = false, $file_types = '.jpg o .png') {
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
            'once' => 'una vez',
            'daily' => 'todos los días',
            'weekly' => 'semanal',
            'monthly' => 'mensual',
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

        $cache_id = __METHOD__ . ':' . $entity_class;
        
        if($app->cache->contains($cache_id)){
            return $app->cache->fetch($cache_id);
        }

        $controller = $app->getControllerByEntity($entity_class);

        if ($entity_class === 'MapasCulturais\Entities\Event') {
            $entities = $controller->apiQueryByLocation(array(
                '@from' => date('Y-m-d'),
                '@to' => date('Y-m-d', time() + 28 * 24 * 3600),
                'isVerified' => 'EQ(true)',
                '@select' => 'id'
            ));
            
        }else{

            $entities = $controller->apiQuery([
                '@select' => 'id',
                'isVerified' => 'EQ(true)'
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
            $q['isVerified'] = 'EQ(true)';
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
            'isVerified' => 'EQ(true)'
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
}
