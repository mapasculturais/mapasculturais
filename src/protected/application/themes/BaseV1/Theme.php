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

    protected function _init(){
        $app = App::i();

        $app->hook('view.render(<<*>>):before', function() use($app) {
            $this->includeCommonAssets();
        });

        $app->hook('view.render(<<agent|space|project|event>>/single):before', function() {
            $this->includeGalleryAssets();
        });

        $app->hook('entity(<<agent|space>>).<<insert|update>>:before', function() use ($app) {

            $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
            $rsm->addScalarResult('nome', 'nome');

            $x = $this->location->longitude;
            $y = $this->location->latitude;

            $whereContains = 'WHERE ST_Contains(the_geom, ST_Transform(ST_GeomFromText(\'POINT(' . $x . ' ' . $y . ')\',4326),4326))';

            $strNativeQuery = 'SELECT nome FROM "sp_regiao" ' . $whereContains;
            $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);
            $this->sp_regiao = $query->getOneOrNullResult()['nome'];

            $strNativeQuery = 'SELECT nome FROM "sp_subprefeitura" ' . $whereContains;
            $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);
            $this->sp_subprefeitura = $query->getOneOrNullResult()['nome'];

            $strNativeQuery = 'SELECT nome_distr AS nome FROM "sp_distrito" ' . $whereContains;
            $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);
            $this->sp_distrito = $query->getOneOrNullResult()['nome'];
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


        $app->hook('repo(<<*>>).getIdsByKeywordDQL.join', function(&$joins){
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

        $app->hook('repo(<<*>>).getIdsByKeywordDQL.where', function(&$where){
            $where .= " OR lower(t.term) LIKE lower(:keyword) ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.join', function(&$joins){
            $joins .= " LEFT JOIN e.project p
                        LEFT JOIN MapasCulturais\Entities\EventMeta m
                            WITH
                                m.key = 'subTitle' AND
                                m.owner = e
                        ";
        });

        $app->hook('repo(Event).getIdsByKeywordDQL.where', function(&$where){
            $where .= " OR lower(p.name) LIKE lower(:keyword)
                        OR lower(m.value) LIKE lower(:keyword)";
        });
    }

    function includeCommonAssets(){
        $this->enqueueStyle('fonts', 'elegant', 'css/elegant-font.css');

        $this->enqueueStyle('vendor', 'select2', 'vendor/select2/select2.css');
        $this->enqueueStyle('vendor', 'x-editable', 'vendor/x-editable/jquery-editable/css/jquery-editable.css', array('select2'));
        $this->enqueueStyle('vendor', 'x-editable-tip', 'vendor/x-editable/jquery-editable/css/tip-yellowsimple.css', array('x-editable'));

        $this->enqueueStyle('app', 'style', 'css/style.css');
        $this->enqueueStyle('app', 'vendor', 'css/vendor.css');

        $this->enqueueScript('vendor', 'mustache', 'vendor/mustache.js');
        $this->enqueueScript('vendor', 'jquery', 'vendor/jquery/jquery-2.0.3.min.js');
        $this->enqueueScript('vendor', 'jquery-slimscroll', 'vendor/jquery.slimscroll.min.js');
        $this->enqueueScript('vendor', 'jquery-form', 'vendor/jquery.form.min.js', array('jquery'));
        $this->enqueueScript('vendor', 'jquery-mask', 'vendor/jquery.mask.min.js', array('jquery'));
        $this->enqueueScript('vendor', 'purl', 'vendor/purl/purl.js', array('jquery'));

        $this->enqueueScript('app', 'tim', 'js/tim.js');
        $this->enqueueScript('app', 'mapasculturais', 'js/mapasculturais.js', array('tim'));

        $this->enqueueScript('vendor', 'select2', 'vendor/select2-3.5.0/select2.min.js', array('jquery'));
        $this->enqueueScript('vendor', 'select2-BR', 'js/select2_locale_pt-BR-edit.js', array('select2'));

        $this->enqueueScript('vendor', 'poshytip', 'vendor/x-editable-jquery-poshytip/jquery.poshytip.js', array('jquery'));
        $this->enqueueScript('vendor', 'x-editable', 'vendor/x-editable-dev-1.5.2/jquery-editable/js/jquery-editable-poshytip.js', array('jquery', 'poshytip', 'select2'));
        $this->enqueueScript('app', 'editable', 'js/editable.js', array('mapasculturais'));

        $this->enqueueScript('vendor', 'angular', 'vendor/angular.js');
        $this->enqueueScript('app', 'notifications', 'js/Notifications.js', array('mapasculturais'));


        if ($app->config('mode') == 'staging')
            $app->enqueueStyle('app', 'staging', 'css/staging.css', array('style'));
    }

    function includeMapAssets() {
        //Leaflet -a JavaScript library for mobile-friendly maps
        $this->enqueueStyle('vendor', 'leaflet', 'vendor/leaflet/lib/leaflet-0.7.3/leaflet.css');
        $this->enqueueScript('vendor', 'leaflet', 'vendor/leaflet/lib/leaflet-0.7.3/leaflet.js');

        //Leaflet Vector Layers
        $this->enqueueScript('vendor', 'leaflet-vector-layers', 'vendor/leaflet-vector-layers/dist/lvector.js', array('leaflet'));

        //Conjuntos de Marcadores
        $this->enqueueStyle('vendor', 'leaflet-marker-cluster', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.css', array('leaflet'));
        $this->enqueueStyle('vendor', 'leaflet-marker-cluster-d', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.Default.css', array('leaflet-marker-cluster'));
        $this->enqueueScript('vendor', 'leaflet-marker-cluster', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/leaflet.markercluster.js', array('leaflet'));

        //Controle de Full Screen
        $this->enqueueStyle('vendor', 'leaflet-fullscreen', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.css', array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-fullscreen', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.js', array('leaflet'));

        //Leaflet Label Plugin
        //$app->enqueueStyle( 'vendor', 'leaflet-label',           'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-label/leaflet.label.css',                                         array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-label', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.label-master/dist/leaflet.label.js', array('leaflet'));

        //Leaflet Draw
        $this->enqueueStyle('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw.css', array('leaflet'));
        $this->enqueueScript('vendor', 'leaflet-draw', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw.js', array('leaflet'));

        $this->enqueueScript('vendor', 'google-maps-api', 'http://maps.google.com/maps/api/js?v=3.2&sensor=false');

        //Leaflet Plugins (Google)false');
        $this->enqueueScript('vendor', 'leaflet-google-tile', 'vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-plugins-master/layer/tile/Google.js', array('leaflet'));
        //Pure CSS Tooltips (Hint - https://github.com/chinchang/hint.css)
        //$this->enqueueStyle('vendor', 'hint', 'http://cdn.jsdelivr.net/hint.css/1.3.0/hint.min.css');
        //Mapa das Singles
        $this->enqueueScript('app', 'map', 'js/map.js');
    }

    function includeAngularEntityAssets($entity){
        $this->enqueueScript('vendor', 'jquery-ui-position', '/vendor/jquery-ui.position.min.js', array('jquery'));

        $this->enqueueScript('vendor', 'angular', '/vendor/angular.js');
        $this->enqueueScript('vendor', 'angular-sanitize', '/vendor/angular-sanitize.min.js', array('angular'));
        $this->enqueueScript('vendor', 'spin.js', '/vendor/spin.min.js', array('angular'));
        $this->enqueueScript('vendor', 'angularSpinner', '/vendor/angular-spinner.min.js', array('spin.js'));

        $this->enqueueScript('app', 'ng-mapasculturais', '/js/ng-mapasculturais.js');
        $this->enqueueScript('app', 'related-agents', '/js/RelatedAgents.js');
        $this->enqueueScript('app', 'change-owner', '/js/ChangeOwner.js');
        $this->enqueueScript('app', 'entity', '/js/Entity.js', array('mapasculturais', 'ng-mapasculturais', 'related-agents', 'change-owner'));

        if(!$this->isEditable())
            return;

        if(isset($this->jsObject['entity']))
            $this->jsObject['entity']['canUserCreateRelatedAgentsWithControl'] = $entity->canUser('createAgentRelationWithControl');
        else
            $this->jsObject['entity'] = array('canUserCreateRelatedAgentsWithControl' => $entity->canUser('createAgentRelationWithControl'));

    }

    function includeGalleryAssets(){
        $this->enqueueScript('vendor', 'magnific-popup', 'vendor/Magnific-Popup-0.9.9/jquery.magnific-popup.min.js', array('jquery'));
        $this->enqueueStyle('vendor', 'magnific-popup', 'vendor/Magnific-Popup-0.9.9/magnific-popup.css');
    }

    /**
    *
    * @param type $file_owner
    * @param type $group_name
    * @param type $response_action
    * @param type $response_target
    * @param type $response_template
    * @param type $response_transform
    * @param type $add_description_input
    */
   function add_ajax_uploader($file_owner, $group_name, $response_action, $response_target, $response_template = '', $response_transform = '', $add_description_input = false, $file_types = '.jpg ou .png') {
       App::i()->view->part('ajax-uploader', array(
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
}