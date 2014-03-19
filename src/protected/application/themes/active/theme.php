<?php
use MapasCulturais\App;
$app = App::i();

function is_editable() {
    return (bool) preg_match('#^\w+/(create|edit)$#', App::i()->view->template);
}

$app->hook('view.render:before', function() use($app){
    $app->enqueueStyle('fonts', 'elegant', '/css/elegant-font.css');

//    if(is_editable()){
        $app->enqueueStyle('vendor', 'select2', '/vendor/select2/select2.css');
        $app->enqueueStyle('vendor', 'x-editable', '/vendor/x-editable/jquery-editable/css/jquery-editable.css', array('select2'));
        $app->enqueueStyle('vendor', 'x-editable-tip', '/vendor/x-editable/jquery-editable/css/tip-yellowsimple.css', array('x-editable'));
//    }

    $app->enqueueStyle('app', 'style', '/css/style.css');

    $app->enqueueScript('vendor',  'mustache'           , '/vendor/mustache.js');
    $app->enqueueScript('vendor',  'jquery'             , '/vendor/jquery/jquery-2.0.3.min.js');
    $app->enqueueScript('vendor',  'jquery-slimscroll'  , '/vendor/jquery.slimscroll.min.js');
    $app->enqueueScript('vendor',  'jquery-form'        , '/vendor/jquery.form.min.js',             array('jquery'));
    $app->enqueueScript('vendor',  'jquery-mask'        , '/vendor/jquery.mask.min.js',             array('jquery'));
    $app->enqueueScript('vendor',  'purl'               , '/vendor/purl/purl.js',                   array('jquery'));

    $app->enqueueScript('app',     'tim'                , '/js/tim.js');
    $app->enqueueScript('app',     'mapasculturais'     , '/js/mapasculturais.js',                  array('tim'));

//    if(is_editable()){
        $app->enqueueScript('vendor', 'select2',            '/vendor/select2/select2.js',       array('jquery'));
        $app->enqueueScript('vendor', 'select2-BR',         '/js/select2_locale_pt-BR-edit.js', array('select2'));

        $app->enqueueScript('vendor', 'poshytip', '/vendor/x-editable/jquery-editable/js/jquery.poshytip.js', array('jquery'));
        $app->enqueueScript('vendor', 'x-editable', '/vendor/x-editable/jquery-editable/js/jquery-editable-poshytip.js', array('jquery', 'poshytip', 'select2'));
        $app->enqueueScript('app', 'editable', '/js/editable.js', array('mapasculturais'));

//    }

    if($app->config('mode') == 'staging')
        $app->enqueueStyle('app', 'staging', '/css/staging.css', array('style'));

});

function add_map_assets(){
    $app = App::i();
    //Leaflet -a JavaScript library for mobile-friendly maps
        $app->enqueueStyle( 'vendor', 'leaflet', '/vendor/leaflet/lib/leaflet-0.7.2/leaflet.css');
        $app->enqueueScript('vendor',  'leaflet',                '/vendor/leaflet/lib/leaflet-0.7.2/leaflet.js');
    //Leaflet Vector Layers
        $app->enqueueScript('vendor',  'leaflet-vector-layers',  '/vendor/leaflet-vector-layers/dist/lvector.js',                                                               array('leaflet'));
    //Conjuntos de Marcadores
        $app->enqueueStyle( 'vendor', 'leaflet-marker-cluster',  '/vendor/leaflet/lib/leaflet-plugins/Leaflet.markercluster-0.4/dist/MarkerCluster.css',                     array('leaflet'));
        $app->enqueueStyle( 'vendor', 'leaflet-marker-cluster-d','/vendor/leaflet/lib/leaflet-plugins/Leaflet.markercluster-0.4/dist/MarkerCluster.Default.css',             array('leaflet-marker-cluster'));
        $app->enqueueScript('vendor', 'leaflet-marker-cluster',  '/vendor/leaflet/lib/leaflet-plugins/Leaflet.markercluster-0.4/dist/leaflet.markercluster.js',          array('leaflet'));
    //Controle de Full Screen
        $app->enqueueStyle( 'vendor', 'leaflet-fullscreen',      '/vendor/leaflet/lib/leaflet-plugins/brunob-leaflet.fullscreen-06c4127/Control.FullScreen.css',                array('leaflet'));
        $app->enqueueScript('vendor', 'leaflet-fullscreen',      '/vendor/leaflet/lib/leaflet-plugins/brunob-leaflet.fullscreen-06c4127/Control.FullScreen.js',                 array('leaflet'));
    //Leaflet Label Plugin
        //$app->enqueueStyle( 'vendor', 'leaflet-label',           '/vendor/leaflet/lib/leaflet-plugins/leaflet-label/leaflet.label.css',                                         array('leaflet'));
        $app->enqueueScript('vendor', 'leaflet-label',           '/vendor/leaflet/lib/leaflet-plugins/leaflet-label/leaflet.label.js',                                          array('leaflet'));
    //Leaflet Draw
        $app->enqueueStyle( 'vendor', 'leaflet-draw',            '/vendor/leaflet/lib/leaflet-plugins/Leaflet.draw-0.2.3/dist/leaflet.draw.css',                               array('leaflet'));
        $app->enqueueScript('vendor', 'leaflet-draw',            '/vendor/leaflet/lib/leaflet-plugins/Leaflet.draw-0.2.3/dist/leaflet.draw.js',                                array('leaflet'));

     //Google Maps API. In dev mode, test internet connection to avoid waiting for host timeout when working without connection
    if($app->config('mode') === 'development' && @fsockopen("maps.google.com", 80))
      $app->enqueueScript('vendor',  'google-maps-api',        'http://maps.google.com/maps/api/js?v=3.2&sensor=false');

    //Leaflet Plugins (Google)false');
        $app->enqueueScript('vendor',  'leaflet-google-tile',    '/vendor/leaflet/lib/leaflet-plugins/leaflet-plugins-master/layer/tile/Google.js',          array('leaflet'));
    //Pure CSS Tooltips (Hint - https://github.com/chinchang/hint.css)
        //$app->enqueueStyle('vendor', 'hint', 'http://cdn.jsdelivr.net/hint.css/1.3.0/hint.min.css');
    //Mapa das Singles
        $app->enqueueStyle( 'app', 'map', '/css/map.css');
        $app->enqueueScript('app', 'map', '/js/map.js');
}


function add_entity_properties_metadata_to_js($entity){
    $class = $entity->className;
    $metadata = $class::getPropertiesMetadata();
    App::i()->hook('mapasculturais.scripts', function() use($metadata){
        ?>
<script type="text/javascript">
    MapasCulturais.Editables.entity = <?php echo json_encode($metadata); ?>;
</script>
        <?php
    });

}

function getOccurrenceFrequencies(){
    return array(
        'once' => 'uma vez',
        'daily' => 'todos os dias',
        'weekly' => 'semanal',
        'monthly' => 'mensal',
    );
}

function add_occurrence_frequencies_to_js(){
    ?>
<script type="text/javascript">
    MapasCulturais.frequencies = <?php echo json_encode(getOccurrenceFrequencies()); ?>;
</script>
    <?php
}



function add_entity_types_to_js($entity){
    App::i()->hook('mapasculturais.scripts', function() use($entity){
        $controller = App::i()->getControllerByEntity($entity);
        $types = $controller->types;

        usort($types, function($a,$b){
            if($a->name > $b->name)
                return 1;
            elseif($a->name < $b->name)
                return -1;
            else
                return 0;
        });
        ?>
<script type="text/javascript">
MapasCulturais.entityTypes = MapasCulturais.entityTypes || {};
MapasCulturais.entityTypes.<?php echo $controller->id ?> = <?php echo json_encode($controller->types); ?>
</script>
        <?php
    });
}

function add_taxonoy_terms_to_js($taxonomy_slug){
    $terms = App::i()->repo('Term')->getTermsAsString($taxonomy_slug);
    App::i()->hook('mapasculturais.scripts', function() use($taxonomy_slug, $terms){
        ?>
<script type="text/javascript">

MapasCulturais.taxonomyTerms = MapasCulturais.taxonomyTerms || {};
MapasCulturais.taxonomyTerms.<?php echo $taxonomy_slug ?> = <?php echo json_encode($terms); ?>
</script>
        <?php
    });
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
function add_ajax_uploader($file_owner, $group_name, $response_action, $response_target, $response_template = '', $response_transform = '', $add_description_input = false){
    App::i()->view->part('parts/ajax-uploader', array(
        'file_owner' => $file_owner,
        'file_group' => $group_name,
        'response_action' => $response_action,
        'response_target' => $response_target,
        'response_template' => $response_template,
        'response_transform' => $response_transform,
        'add_description' => $add_description_input
    ));
}


/**
 *
 * @param type $file_owner
 * @param type $group_name
 * @param type $response_action
 * @param type $response_target
 * @param type $response_template
 * @param type $response_transform
 * @param type $add_description_input1
 */
function add_metalist_manager($object, $metalist_group, $metalist_action, $response_action, $response_target, $response_template = '', $response_transform = '', $add_description_input = false){

    App::i()->view->part('parts/metalist-manager', array(
        'object' => $object,
        'metalist_group' => $metalist_group,
        'metalist_action' => $metalist_action,
        'response_action' => $response_action,
        'response_target' => $response_target,
        'response_template' => $response_template,
        'response_transform' => $response_transform,
        'add_description' => $add_description_input
    ));
}
/*
MapasCulturais\App::i()->hook('controller(agent).render(single)', function(&$template){
    $template = 'edit';

});
 */


$agentHookCallback = function() use ($app){

    $rsm = new \Doctrine\ORM\Query\ResultSetMapping();
    $rsm->addScalarResult('nome','nome');

    $x = $this->location->longitude;
    $y = $this->location->latitude;

    $whereContains = 'WHERE ST_Contains(the_geom, ST_Transform(ST_GeomFromText(\'POINT('.$x.' '.$y.')\',4326),4326))';

    $strNativeQuery = 'SELECT nome FROM "sp_regiao" '.$whereContains;
    $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);
    $this->sp_regiao = $query->getOneOrNullResult()['nome'];

    $strNativeQuery = 'SELECT nome FROM "sp_subprefeitura" '.$whereContains;
    $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);
    $this->sp_subprefeitura = $query->getOneOrNullResult()['nome'];

    $strNativeQuery = 'SELECT nome_distr AS nome FROM "sp_distrito" '.$whereContains;
    $query = $app->getEm()->createNativeQuery($strNativeQuery, $rsm);
    $this->sp_distrito = $query->getOneOrNullResult()['nome'];

};


$app->hook('post(agent.single):after', function(){});

$app->hook('entity(agent).update:before',  $agentHookCallback);

$app->hook('entity(agent).insert:before', $agentHookCallback);

$app->hook('entity(space).update:before',  $agentHookCallback);

$app->hook('entity(space).insert:before', $agentHookCallback);

//// altera as imagens atuais
$app->hook('entity(agent).file(avatar).load', function(){
   if(!$this->transform('avatarBig')->path);
});

$app->hook('entity(space).file(avatar).load', function(){
   $this->transform('avatarSmall');
   $this->transform('resultadoBusca');
});

// sempre que insere uma imagem cria o avatarSmall
$app->hook('entity(agent).file(avatar).insert:after', function(){
    $this->transform('avatarSmall');
    $this->transform('avatarBig');
});

$app->hook('entity(space).file(avatar).insert:after', function(){
    $this->transform('avatarSmall');
    $this->transform('avatarBig');
});

App::i()->hook('entity(event).file(avatar).insert:after', function(){
    $this->transform('avatarSmall');
    $this->transform('avatarBig');
});

App::i()->hook('entity(project).file(avatar).insert:after', function(){
    $this->transform('avatarSmall');
    $this->transform('avatarBig');
});


$app->hook('entity(space).file(header).insert:after', function(){
    $this->transform('header');
});

$app->hook('entity(agent).file(header).insert:after', function(){
    $this->transform('header');
});

$app->hook('entity(event).file(header).insert:after', function(){
    $this->transform('header');
});


$app->hook('entity(project).file(header).insert:after', function(){
    $this->transform('header');
});

$app->hook('entity.file(gallery).insert:after', function(){
    $this->transform('galleryThumb');
    $this->transform('galleryFull');
});

if(key_exists('opauth.OpenID.logoutUrl', $app->config) && $app->config['opauth.OpenID.logoutUrl']){
    $app->hook('auth.logout', function(){
        $_SESSION['openid.logout'] = true;
    });

    if(key_exists('openid.logout', $_SESSION)){

        unset($_SESSION['openid.logout']);
        $app->hook('mapasculturais.body:before', function() use ($app){
            $url = $app->config['opauth.OpenID.logoutUrl'];
            $app->redirect($url);
        });
    }
}


$app->hook('entity(event).load', function(){
    $this->type = 1;
});


$app->hook('entity(event).save:before', function(){
    $this->type = 1;
});