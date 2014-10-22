<?php

namespace MapasCulturais\Themes\BaseV1;

use MapasCulturais;
use MapasCulturais\App;

class Theme extends MapasCulturais\Theme {

    static function getThemeFolder() {
        return __DIR__;
    }

    protected function _init() {
        $app = App::i();

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->jsObject['assets'] = array();
            $this->jsObject['templateUrl'] = array();
            $this->jsObject['spinnerUrl'] = $this->asset('img/spinner.gif', false);

            $this->jsObject['mapsDefaults'] = array(
                'zoomMax' => $app->config['maps.zoom.max'],
                'zoomMin' => $app->config['maps.zoom.min'],
                'zoomDefault' => $app->config['maps.zoom.default'],
                'zoomPrecise' => $app->config['maps.zoom.precise'],
                'zoomApproximate' => $app->config['maps.zoom.approximate'],

                'includeGoogleLayers' => $app->config['maps.includeGoogleLayers'],

                'latitude' => $app->config['maps.center'][0],
                'longitude'  => $app->config['maps.center'][1]
            );


            $this->addDocumentMetas();
            $this->includeVendorAssets();
            $this->includeCommonAssets();
            $this->_populateJsObject();
        });

        $app->hook('view.render(<<agent|space|project|event>>/<<single|edit|create>>):before', function() {
            $this->jsObject['templateUrl']['editBox'] = $this->asset('js/directives/edit-box.html', false);
            $this->jsObject['templateUrl']['findEntity'] = $this->asset('js/directives/find-entity.html', false);
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

            foreach($app->getRegisteredGeoDivisions() as $d){
                $metakey = $d->metakey;
                $this->$metakey = '';
            }

            foreach($divisions as $div){
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


        $app->hook('repo(<<*>>).getIdsByKeywordDQL.join', function(&$joins) {
            $taxonomy = App::i()->getRegisteredTaxonomyBySlug('tag');

            $class = $this->getClassName();

            $joins .= "LEFT JOIN
                        MapasCulturais\Entities\TermRelation
                            tr
                        WITH
                            tr.objectType = '$class' AND
                            tr.objectId = e.id
                            LEFT JOIN
                                tr.term
                                    t
                                WITH
                                    t.taxonomy = '{$taxonomy->id}'";
        });

        $app->hook('repo(<<*>>).getIdsByKeywordDQL.where', function(&$where) {
            $where .= " OR lower(t.term) LIKE lower(:keyword) ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.join', function(&$joins) {
            $joins .= " LEFT JOIN e.project p
                        LEFT JOIN MapasCulturais\Entities\EventMeta m
                            WITH
                                m.key = 'subTitle' AND
                                m.owner = e
                        ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.where', function(&$where) {
            $where .= " OR lower(p.name) LIKE lower(:keyword)
                        OR lower(m.value) LIKE lower(:keyword)";
        });
    }

    function register(){
        $app = App::i();
        foreach($app->config['app.geoDivisionsHierarchy'] as $slug => $name){
            foreach(array('MapasCulturais\Entities\Agent', 'MapasCulturais\Entities\Space') as $entity_class){
                $entity_types = $app->getRegisteredEntityTypes($entity_class);

                foreach($entity_types as $type){
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
        $this->printStyles('fonts');
        $this->printStyles('app');

        $app->applyHook('mapasculturais.styles');

        $this->_printJsObject();

        $this->printScripts('vendor');
        $this->printScripts('app');

        $app->applyHook('mapasculturais.scripts');
    }

    function addDocumentMetas() {
        $app = App::i();
        $entity = $this->controller->requiredEntity;

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
        $this->documentMeta[] = array("property" => 'og:image', 'content' => $title);
        $this->documentMeta[] = array("property" => 'og:type', 'content' => 'article');
        $this->documentMeta[] = array("property" => 'og:image', 'content' => $image_url);
        $this->documentMeta[] = array("property" => 'og:description', 'content' => $description);
        $this->documentMeta[] = array("property" => 'og:site_name', 'content' => $site_name);

        if ($entity) {
            $this->documentMeta[] = array("property" => 'og:url', 'content' => $entity->singleUrl);
            $this->documentMeta[] = array("property" => 'og:published_time', 'content' => $entity->createTimestamp->format('Y-m-d'));

            // @TODO: modified time is not implemented
            // $this->documentMeta[] = array( "property" => 'og:modified_time',   'content' => $entity->modifiedTimestamp->format('Y-m-d'));
        }
    }

    function includeVendorAssets(){
        $this->enqueueStyle('vendor', 'x-editable', 'vendor/x-editable/jquery-editable/css/jquery-editable.css', array('select2'));
        $this->enqueueStyle('vendor', 'x-editable-tip', 'vendor/x-editable/jquery-editable/css/tip-yellowsimple.css', array('x-editable'));

        $this->enqueueScript('vendor', 'mustache', 'vendor/mustache.js');

        $this->enqueueScript('vendor', 'jquery', 'vendor/jquery-2.1.1.js');
        $this->enqueueScript('vendor', 'jquery-slimscroll', 'vendor/jquery.slimscroll.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-form', 'vendor/jquery.form.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-mask', 'vendor/jquery.mask.js', array('jquery'));
        $this->enqueueScript('vendor', 'purl', 'vendor/purl/purl.js', array('jquery'));

        // select 2
        $this->enqueueStyle('vendor', 'select2', 'vendor/select2-3.5.0/select2.css');
        $this->enqueueScript('vendor', 'select2', 'vendor/select2-3.5.0/select2.js', array('jquery'));

        $this->enqueueScript('vendor', 'select2-BR', 'vendor/select2_locale_pt-BR-edit.js', array('select2'));

        $this->enqueueScript('vendor', 'poshytip', 'vendor/x-editable-jquery-poshytip/jquery.poshytip.js', array('jquery'));
        $this->enqueueScript('vendor', 'x-editable', 'vendor/x-editable-dev-1.5.2/jquery-editable/js/jquery-editable-poshytip.js', array('jquery', 'poshytip', 'select2'));


        //Leaflet -a JavaScript library for mobile-friendly maps
        $this->enqueueStyle('vendor', 'leaflet', 'vendor/leaflet/lib/leaflet-0.7.3/leaflet.css');
        $this->enqueueScript('vendor', 'leaflet', 'vendor/leaflet/lib/leaflet-0.7.3/leaflet-src.js');

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

        $this->enqueueStyle('vendor', 'magnific-popup', 'vendor/Magnific-Popup-0.9.9/magnific-popup.css');
        $this->enqueueScript('vendor', 'magnific-popup', 'vendor/Magnific-Popup-0.9.9/jquery.magnific-popup.js', array('jquery'));

        $this->enqueueScript('vendor', 'momentjs', 'vendor/moment.js');
        $this->enqueueScript('vendor', 'momentjs-pt-br', 'vendor/moment.pt-br.js', array('momentjs'));

        $this->enqueueScript('vendor', 'jquery-ui-core', 'vendor/jquery-ui-1.11.1/core.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-ui-position', 'vendor/jquery-ui-1.11.1/position.js', array('jquery-ui-core'));
        $this->enqueueScript('vendor', 'jquery-ui-datepicker', 'vendor/jquery-ui-1.11.1/datepicker.js', array('jquery-ui-core'));
        $this->enqueueScript('vendor', 'jquery-ui-datepicker-pt-BR', 'vendor/jquery-ui-1.11.1/datepicker-pt-BR.js', array('jquery-ui-datepicker'));

        $angular_version = '1.2.26';

        $this->enqueueScript('vendor', 'angular', "vendor/angular-{$angular_version}/angular.js", array('jquery', 'jquery-ui-datepicker-pt-BR', 'jquery-ui-position'));
        $this->enqueueScript('vendor', 'angular-sanitize', "vendor/angular-{$angular_version}/angular-sanitize.js", array('angular'));

        $this->enqueueScript('vendor', 'angular-rison', '/vendor/angular-rison.js', array('angular'));
        $this->enqueueScript('vendor', 'ng-infinite-scroll', '/vendor/ng-infinite-scroll/ng-infinite-scroll.js', array('angular'));

        $this->enqueueScript('vendor', 'angular-ui-date', '/vendor/ui-date-master/src/date.js', array('jquery-ui-datepicker-pt-BR', 'angular'));


        $this->enqueueScript('vendor', 'spin.js', 'vendor/spin.js', array('angular'));
        $this->enqueueScript('vendor', 'angular-spinner', 'vendor/angular-spinner.js', array('spin.js'));


    }

    function includeCommonAssets() {
        $this->getAssetManager()->publishFolder('fonts/');

        $this->enqueueStyle('fonts', 'elegant', 'css/elegant-font.css');

        $this->enqueueStyle('app', 'style', 'css/style.css');
        $this->enqueueStyle('app', 'vendor', 'css/vendor.css');

        $this->enqueueScript('app', 'tim', 'js/tim.js');
        $this->enqueueScript('app', 'mapasculturais', 'js/mapasculturais.js', array('tim'));

        $this->enqueueScript('app', 'ng-mapasculturais', 'js/ng-mapasculturais.js');
        $this->enqueueScript('app', 'notifications', 'js/Notifications.js', array('ng-mapasculturais'));

        if($this->isEditable())
            $this->includeEditableEntityAssets ();


        if (App::i()->config('mode') == 'staging')
            $this->enqueueStyle('app', 'staging', 'css/staging.css', array('style'));
    }

    function includeEditableEntityAssets(){
        $this->enqueueScript('app', 'editable', 'js/editable.js', array('mapasculturais'));

    }

    function includeSearchAssets(){

        $this->enqueueScript('app', 'SearchService', 'js/SearchService.js', array('ng-mapasculturais', 'SearchSpatial'));
        $this->enqueueScript('app', 'FindOneService', 'js/FindOneService.js', array('ng-mapasculturais', 'SearchSpatial'));
        $this->enqueueScript('app', 'SearchMapController', 'js/SearchMap.js', array('ng-mapasculturais', 'map'));
        $this->enqueueScript('app', 'SearchSpatial', 'js/SearchSpatial.js', array('ng-mapasculturais', 'map'));

        $this->enqueueScript('app', 'Search', 'js/Search.js', array('ng-mapasculturais', 'SearchSpatial', 'SearchMapController', 'FindOneService', 'SearchService'));
    }

    function includeMapAssets() {
        $app = App::i();

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

    function includeAngularEntityAssets($entity){
        $this->enqueueScript('app', 'change-owner', 'js/ChangeOwner.js', array('ng-mapasculturais'));
        $this->enqueueScript('app', 'entity', 'js/Entity.js', array('mapasculturais', 'ng-mapasculturais', 'change-owner'));

        if(!$this->isEditable()){
            return;
        }


        $this->jsObject['request']['id'] = $entity->id;

        $this->jsObject['entity'] = array(
            'id' => $entity->id,
            'ownerId' => $entity->owner->id, // ? $entity->owner->id : null,
            'ownerUserId' => $entity->ownerUser->id,
            'definition' => $entity->getPropertiesMetadata(),
            'userHasControl' => $entity->canUser('@control'),
            'canUserCreateRelatedAgentsWithControl' => $entity->canUser('createAgentRelationWithControl'),

        );
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

    protected function _populateJsObject(){

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

        if(!isset($this->jsObject['entityTypes']))
            $this->jsObject['entityTypes'] = array();

        $this->jsObject['entityTypes'][$controller->id] = $types;

    }

    function addTaxonoyTermsToJs($taxonomy_slug) {
        $terms = App::i()->repo('Term')->getTermsAsString($taxonomy_slug);
        if(!isset($this->jsObject['taxonomyTerms']))
            $this->jsObject['taxonomyTerms'] = array();

        $this->jsObject['taxonomyTerms'][$taxonomy_slug] = $terms;
    }

    function addRelatedAgentsToJs($entity){
        $this->jsObject['entity']['agentRelations'] = $entity->getAgentRelationsGrouped(null, $this->isEditable());
    }
}
