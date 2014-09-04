<?php
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Notification;

$app = App::i();

/* === NOTIFICATIONS  === */

// Requests to create event occurrence in spaces

$app->hook('workflow(RequestEventOccurrence).create', function() use($app){
    $agent = $app->user->profile;
    $event = $this->targetEntity;
    $space = $this->destinationSpace;
    
    $description = $this->rule->description;
    
    $message = "<a href=\"{$agent->singleUrl}\">{$agent->name}</a> quer adicionar o evento <a href=\"{$event->singleUrl}\">{$event->name}</a> que ocorre <em>{$description}</em> no espaço <a href=\"{$space->singleUrl}\">{$space->name}</a>";

    foreach($space->usersWithControl as $user){
        $notification = new Notification;
        $notification->user = $user;
        $notification->message = $message;
        $notification->request = $this;
        $notification->save(true);
    }

    if(!$app->user->equals($this->targetEntity->ownerUser)){
        $notification = new Notification;
        $notification->user = $this->targetEntity->ownerUser;
        $notification->message = $message;
        $notification->request = $this;
        $notification->save(true);
    }

});

$app->hook('workflow(RequestEventOccurrence).approve:before', function() use($app){
    $agent = $app->user->profile;
    $event = $this->targetEntity;
    $space = $this->destinationSpace;
    
    $description = $this->rule->description;
    
    $message = "<a href=\"{$agent->singleUrl}\">{$agent->name}</a> autorizou o evento <a href=\"{$event->singleUrl}\">{$event->name}</a> que ocorre <em>{$description}</em> no espaço <a href=\"{$space->singleUrl}\">{$space->name}</a>";

    $users = array();
    
    $users[] = $this->requesterUser;
    
    // se não foi o dono do evento que fez a requisição, notifica o dono
    if(!$event->ownerUser->equals($this->requesterUser))
        $users[] = $space->ownerUser;
    
    // se não é o dono do espaço que está aprovando, notifica o dono
    if(!$space->ownerUser->equals($app->user))
        $users[] = $space->ownerUser;
    
    $notified_user_ids = array();

    foreach($users as $u){
        // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
        if(in_array($u->id, $notified_user_ids))
                continue;

        $notified_user_ids[] = $u->id;

        $notification = new Notification;
        $notification->message = $message;
        $notification->user = $u;
        $notification->save(true);
    }
});

$app->hook('workflow(RequestEventOccurrence).reject:before', function() use($app){
    $agent = $app->user->profile;
    $event = $this->targetEntity;
    $space = $this->destinationSpace;
    
    $description = $this->rule->description;
    
    $message = "<a href=\"{$agent->singleUrl}\">{$agent->name}</a> rejeitou o evento <a href=\"{$event->singleUrl}\">{$event->name}</a> que ocorre <em>{$description}</em> no espaço <a href=\"{$space->singleUrl}\">{$space->name}</a>";

    $users = array();
    
    $users[] = $this->requesterUser;
    
    // se não foi o dono do evento que fez a requisição, notifica o dono
    if(!$event->ownerUser->equals($this->requesterUser))
        $users[] = $space->ownerUser;
    
    // se não é o dono do espaço que está rejeitando, notifica o dono
    if(!$space->ownerUser->equals($app->user))
        $users[] = $space->ownerUser;
    
    $notified_user_ids = array();

    foreach($users as $u){
        // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
        if(in_array($u->id, $notified_user_ids))
                continue;

        $notified_user_ids[] = $u->id;

        $notification = new Notification;
        $notification->message = $message;
        $notification->user = $u;
        $notification->save(true);
    }
});



// Requests to change ownership of entities
$app->hook('workflow(RequestChangeOwnership).create', function() use($app){
    $entity_type = strtolower($this->targetEntity->entityType);

    if($this->type === Entities\RequestChangeOwnership::TYPE_GIVE)
        $message = "<a href=\"{$app->user->profile->singleUrl}\">{$app->user->profile->name}</a> quer passar a propriedade do {$entity_type} <a href=\"{$this->targetEntity->singleUrl}\">{$this->targetEntity->name}</a> para o agente <a href=\"{$this->destinationAgent->singleUrl}\">{$this->destinationAgent->name}</a>";
    else
        $message = "<a href=\"{$this->destinationAgent->singleUrl}\">{$this->destinationAgent->name}</a> está requisitando a propriedade do {$entity_type} <a href=\"{$this->targetEntity->singleUrl}\">{$this->targetEntity->name}</a>";

    foreach($this->destinationAgent->usersWithControl as $user){
        $notification = new Notification;
        $notification->user = $user;
        $notification->message = $message;
        $notification->request = $this;
        $notification->save(true);
    }

    if(!$app->user->equals($this->targetEntity->ownerUser)){
        $notification = new Notification;
        $notification->user = $this->targetEntity->ownerUser;
        $notification->message = $message;
        $notification->request = $this;
        $notification->save(true);
    }
});

$app->hook('workflow(RequestChangeOwnership).approve:before', function() use($app){
    $entity_type = strtolower($this->targetEntity->entityType);

    if($this->type === Entities\RequestChangeOwnership::TYPE_GIVE)
        $message = "<a href=\"{$app->user->profile->singleUrl}\">{$app->user->profile->name}</a> aceitou a mudança de propriedade do {$entity_type} <a href=\"{$this->targetEntity->singleUrl}\">{$this->targetEntity->name}</a> para o agente <a href=\"{$this->destinationAgent->singleUrl}\">{$this->destinationAgent->name}</a>";
    else
        $message = "<a href=\"{$app->user->profile->singleUrl}\">{$app->user->profile->name}</a> cedeu a propriedade do {$entity_type} <a href=\"{$this->targetEntity->singleUrl}\">{$this->targetEntity->name}</a>";


    $users = array();

    // o usuário que fez a requisição recebe notificação
    $users[] = $this->requesterUser;

    // se o usuário que fez a requisição não é o dono da entidade, ele também recebe a notificação
    if($this->type === Entities\RequestChangeOwnership::TYPE_GIVE && !$this->requesterUser->equals($this->targetEntity->ownerUser))
        $users[] = $this->targetEntity->ownerUser;

    // se o usuário que está aprovando a requisição não é o dono do agente de destino, notifica o dono do agente de destino
    if($this->type === Entities\RequestChangeOwnership::TYPE_GIVE && !$this->requesterUser->equals($this->targetEntity->ownerUser))
        $users[] = $this->destinationAgent->ownerUser;

    // se o usuário que fez a requisição não é o dono da entidade, ele também recebe a notificação
    if($this->type === Entities\RequestChangeOwnership::TYPE_REQUEST && !$this->destinationAgent->user->equals($app->user))
        $users[] = $this->destinationAgent->user;

    // se o usuário que está aprovando a requisição não é o dono da entidade, notifica o dono da entidade
    if($this->type === Entities\RequestChangeOwnership::TYPE_REQUEST && !$this->targetEntity->ownerUser->equals($app->user))
        $users[] = $this->targetEntity->user;

    $notified_user_ids = array();

    foreach($users as $u){
        // impede que a notificação seja entregue mais de uma vez ao mesmo usuário se as regras acima se somarem
        if(in_array($u->id, $notified_user_ids))
                continue;

        $notified_user_ids[] = $u->id;

        $notification = new Notification;
        $notification->message = $message;
        $notification->user = $u;
        $notification->save(true);
    }

});


$app->hook('workflow(RequestChangeOwnership).reject:before', function() use($app){
    $notification = new Notification;

    $entity_type = strtolower($this->targetEntity->entityType);

    if($this->type === Entities\RequestChangeOwnership::TYPE_GIVE)
        $message = "O usuário <a href=\"{$app->user->profile->singleUrl}\">{$app->user->profile->name}</a> rejeitou a propriedade do {$entity_type} <a href=\"{$this->targetEntity->singleUrl}\">{$this->targetEntity->name}</a>";
    else
        $message = "O usuário <a href=\"{$app->user->profile->singleUrl}\">{$app->user->profile->name}</a> não cedeu a propriedade do {$entity_type} <a href=\"{$this->targetEntity->singleUrl}\">{$this->targetEntity->name}</a>";

    $notification->user = $this->requesterUser;
    $notification->message = $message;
    $notification->save(true);


    if(!$app->user->equals($this->targetEntity->ownerUser) && !$this->requesterUser->equals($this->targetEntity->ownerUser)){
        // if the user that approve the request is not the onwer user of the entity, notify the owner
        $notification2 = new Notification;
        $notification2->message = $message;
        $notification2->user = $this->targetEntity->ownerUser;
        $notification2->save(true);
    }
});

/* ---------------------- */




function is_editable() {
    return (bool) preg_match('#^\w+/(create|edit)$#', App::i()->view->template);
}

function mapasculturais_head($entity = null){
    $app = App::i();
    $site_name = $app->siteName;

    $title = htmlentities($app->view->getTitle($entity));
    $image_url = $app->view->asset('img/share.png', false);
    if($entity){
        $description = $entity->shortDescription ? htmlentities($entity->shortDescription) : $title;
        if($entity->avatar)
            $image_url = $entity->avatar->transform('avatarBig')->url;
    }else{
        $description = htmlentities($app->siteDescription);
    }

    ?>
    <!-- for Google -->
    <meta name="description" content="<?php echo $description ?>" />
    <meta name="keywords" content="<?php echo $site_name ?>" />

    <meta name="author" content="<?php echo $site_name ?>" />
    <meta name="copyright" content="<?php echo $site_name ?>" />
    <meta name="application-name" content="<?php echo $site_name ?>" />

    <!-- for Google+ -->
    <meta itemprop="name" content="<?php echo $title ?>">
    <meta itemprop="description" content="<?php echo $description ?>">
    <meta itemprop="image" content="<?php echo $image_url ?>">

    <!-- for Twitter -->
    <meta name="twitter:card" content="summary" />
    <meta name="twitter:title" content="<?php echo $title;?>" />
    <meta name="twitter:description" content="<?php echo $description ?>" />
    <meta name="twitter:image" content="<?php echo $image_url ?>" />

    <!-- for Facebook -->
    <meta property="og:title" content="<?php echo $title ?>" />
    <meta property="og:type" content="article" />
    <meta property="og:image" content="<?php echo $image_url ?>" />
    <meta property="og:description" content="<?php echo $description ?>" />
    <meta property="og:site_name" content="<?php echo $site_name ?>" />
    <?php if($entity): ?>
        <meta property="og:url" content="<?php echo $entity->singleUrl; ?>" />
        <meta property="article:published_time" content="<?php echo $entity->createTimestamp->format('Y-m-d') ?>" />
        <meta property="article:modified_time" content="2013-09-16T19:08:47+01:00" />
    <?php endif; ?>

    <?php $app->applyHook('mapasculturais.head'); ?>

    <script type="text/javascript">
        var MapasCulturais = {
            baseURL: '<?php echo $app->baseUrl ?>',
            vectorLayersURL: "<?php echo $app->baseUrl . $app->config['vectorLayersPath']; ?>",
            assetURL: '<?php echo $app->assetUrl ?>',
            request: {
                controller: '<?php if ($app->view->controller) echo $app->view->controller->id ?>',
                action: '<?php if ($app->view->controller) echo str_replace($app->view->controller->id . '/', '', $app->view->template) ?>',
                id: <?php echo ($entity && $entity->id) ? $entity->id : 'null'; ?>,
            },
            <?php if($entity && is_editable()): ?>
            entity: {
                id: <?php echo $entity->id ? $entity->id : 'null' ?>,
                ownerId: <?php echo $entity->owner->id ? $entity->owner->id : 'null' ?>,
                ownerUserId: <?php echo $entity->ownerUser->id ? $entity->ownerUser->id : 'null' ?>
            },
            <?php endif; ?>
            mode: "<?php echo $app->config('mode'); ?>"
        };
    </script>
    <?php
    $app->printStyles('vendor');
    $app->printStyles('fonts');
    $app->printStyles('app');
    $app->printScripts('vendor');
    $app->printScripts('app');

    $app->applyHook('mapasculturais.scripts');
}

function body_properties(){
    $app = App::i();
    $body_properties = array();
    foreach ($app->view->bodyProperties as $key => $val)
        $body_properties[] = "{$key}=\"$val\"";
    $body_properties[] = 'class="' . implode(' ', $app->view->bodyClasses->getArrayCopy()) . '"';

    $body_properties = implode(' ', $body_properties);

    echo $body_properties;
}

function body_header(){
    App::i()->applyHook('mapasculturais.body:before');
}

function body_footer(){
    $app = App::i();
    $app->view->part('templates');
    $app->applyHook('mapasculturais.body:after');
    ?>
    <iframe id="require-authentication" src="" style="display:none; position:fixed; top:0%; left:0%; width:100%; height:100%; z-index:100000"></iframe>
    <?php
}

$app->hook('view.render(<<agent|space|project|event>>/single):before', function() use ($app) {
    $app->enqueueScript('vendor', 'magnific-popup', '/vendor/Magnific-Popup-0.9.9/jquery.magnific-popup.min.js', array('jquery'));
    $app->enqueueStyle('vendor', 'magnific-popup', '/vendor/Magnific-Popup-0.9.9/magnific-popup.css');
});

$app->hook('controller(<<agent|project|space|event>>).render(<<single|edit>>)', function() use($app){
     $app->hook('mapasculturais.body:before', function(){ ?>
            <!--facebook compartilhar-->
            <div id="fb-root"></div>
            <script>(function(d, s, id) {
                    var js, fjs = d.getElementsByTagName(s)[0];
                    if (d.getElementById(id))
                        return;
                    js = d.createElement(s);
                    js.id = id;
                    js.src = "//connect.facebook.net/pt_BR/all.js#xfbml=1";
                    fjs.parentNode.insertBefore(js, fjs);
                }(document, 'script', 'facebook-jssdk'));</script>
            <!--fim do facebook-->
     <?php });
});

$app->hook('view.render(<<*>>):before', function() use($app) {
    $app->enqueueStyle('fonts', 'elegant', '/css/elegant-font.css');

    $app->enqueueStyle('vendor', 'select2', '/vendor/select2/select2.css');
    $app->enqueueStyle('vendor', 'x-editable', '/vendor/x-editable/jquery-editable/css/jquery-editable.css', array('select2'));
    $app->enqueueStyle('vendor', 'x-editable-tip', '/vendor/x-editable/jquery-editable/css/tip-yellowsimple.css', array('x-editable'));

    $app->enqueueStyle('app', 'style', '/css/style.css');
    $app->enqueueStyle('app', 'vendor', '/css/vendor.css');

    $app->enqueueScript('vendor', 'mustache', '/vendor/mustache.js');
    $app->enqueueScript('vendor', 'jquery', '/vendor/jquery/jquery-2.0.3.min.js');
    $app->enqueueScript('vendor', 'jquery-slimscroll', '/vendor/jquery.slimscroll.min.js');
    $app->enqueueScript('vendor', 'jquery-form', '/vendor/jquery.form.min.js', array('jquery'));
    $app->enqueueScript('vendor', 'jquery-mask', '/vendor/jquery.mask.min.js', array('jquery'));
    $app->enqueueScript('vendor', 'purl', '/vendor/purl/purl.js', array('jquery'));

    $app->enqueueScript('app', 'tim', '/js/tim.js');
    $app->enqueueScript('app', 'mapasculturais', '/js/mapasculturais.js', array('tim'));

    $app->enqueueScript('vendor', 'select2', '/vendor/select2-3.5.0/select2.min.js', array('jquery'));
    $app->enqueueScript('vendor', 'select2-BR', '/js/select2_locale_pt-BR-edit.js', array('select2'));

    $app->enqueueScript('vendor', 'poshytip', '/vendor/x-editable-jquery-poshytip/jquery.poshytip.js', array('jquery'));
    $app->enqueueScript('vendor', 'x-editable', '/vendor/x-editable-dev-1.5.2/jquery-editable/js/jquery-editable-poshytip.js', array('jquery', 'poshytip', 'select2'));
    $app->enqueueScript('app', 'editable', '/js/editable.js', array('mapasculturais'));

    if ($app->config('mode') == 'staging')
        $app->enqueueStyle('app', 'staging', '/css/staging.css', array('style'));
});

function add_map_assets() {
    $app = App::i();
    //Leaflet -a JavaScript library for mobile-friendly maps
    $app->enqueueStyle('vendor', 'leaflet', '/vendor/leaflet/lib/leaflet-0.7.3/leaflet.css');
    $app->enqueueScript('vendor', 'leaflet', '/vendor/leaflet/lib/leaflet-0.7.3/leaflet.js');
    //Leaflet Vector Layers
    $app->enqueueScript('vendor', 'leaflet-vector-layers', '/vendor/leaflet-vector-layers/dist/lvector.js', array('leaflet'));
    //Conjuntos de Marcadores
    $app->enqueueStyle('vendor', 'leaflet-marker-cluster', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.css', array('leaflet'));
    $app->enqueueStyle('vendor', 'leaflet-marker-cluster-d', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/MarkerCluster.Default.css', array('leaflet-marker-cluster'));
    $app->enqueueScript('vendor', 'leaflet-marker-cluster', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.markercluster-master/dist/leaflet.markercluster.js', array('leaflet'));
    //Controle de Full Screen
    $app->enqueueStyle('vendor', 'leaflet-fullscreen', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.css', array('leaflet'));
    $app->enqueueScript('vendor', 'leaflet-fullscreen', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet.fullscreen-master/Control.FullScreen.js', array('leaflet'));
    //Leaflet Label Plugin
    //$app->enqueueStyle( 'vendor', 'leaflet-label',           '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-label/leaflet.label.css',                                         array('leaflet'));
    $app->enqueueScript('vendor', 'leaflet-label', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.label-master/dist/leaflet.label.js', array('leaflet'));
    //Leaflet Draw
    $app->enqueueStyle('vendor', 'leaflet-draw', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw.css', array('leaflet'));
    $app->enqueueScript('vendor', 'leaflet-draw', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/Leaflet.draw-master/dist/leaflet.draw.js', array('leaflet'));

    //Google Maps API. In dev mode, test internet connection to avoid waiting for host timeout when working without connection
//    if ($app->config('mode') !== 'development' || @fsockopen("maps.google.com", 80))
        $app->enqueueScript('vendor', 'google-maps-api', 'http://maps.google.com/maps/api/js?v=3.2&sensor=false');

    //Leaflet Plugins (Google)false');
    $app->enqueueScript('vendor', 'leaflet-google-tile', '/vendor/leaflet/lib/leaflet-plugins-updated-2014-07-25/leaflet-plugins-master/layer/tile/Google.js', array('leaflet'));
    //Pure CSS Tooltips (Hint - https://github.com/chinchang/hint.css)
    //$app->enqueueStyle('vendor', 'hint', 'http://cdn.jsdelivr.net/hint.css/1.3.0/hint.min.css');
    //Mapa das Singles
    $app->enqueueScript('app', 'map', '/js/map.js');
}

function add_angular_entity_assets($entity){
    $app = App::i();
    $app->enqueueScript('vendor', 'jquery-ui-position', '/vendor/jquery-ui.position.min.js', array('jquery'));

    $app->enqueueScript('vendor', 'angular', '/vendor/angular.js');
    $app->enqueueScript('vendor', 'angular-sanitize', '/vendor/angular-sanitize.min.js', array('angular'));
    $app->enqueueScript('vendor', 'spin.js', '/vendor/spin.min.js', array('angular'));
    $app->enqueueScript('vendor', 'angularSpinner', '/vendor/angular-spinner.min.js', array('spin.js'));

    $app->enqueueScript('app', 'ng-mapasculturais', '/js/ng-mapasculturais.js');
    $app->enqueueScript('app', 'related-agents', '/js/RelatedAgents.js');
    $app->enqueueScript('app', 'entity', '/js/Entity.js', array('mapasculturais', 'ng-mapasculturais', 'related-agents'));
    if(!is_editable())
        return;

    App::i()->hook('mapasculturais.scripts', function() use($app, $entity) {
        $isEntityOwner = $entity->ownerUser->id === $app->user->id;
        ?>
        <script type="text/javascript">
            MapasCulturais.entity = MapasCulturais.entity || {};
            MapasCulturais.entity.canUserCreateRelatedAgentsWithControl = <?php echo $entity->canUser('createAgentRelationWithControl') ? 'true' : 'false' ?>;
        </script>
        <?php
    });
}

function add_entity_properties_metadata_to_js($entity) {
    $class = $entity->className;
    $metadata = $class::getPropertiesMetadata();
    App::i()->hook('mapasculturais.scripts', function() use($metadata) {
        ?>
        <script type="text/javascript">
            MapasCulturais.Editables.entity = <?php echo json_encode($metadata); ?>;
        </script>
        <?php
    });
}

function getOccurrenceFrequencies() {
    return array(
        'once' => 'uma vez',
        'daily' => 'todos os dias',
        'weekly' => 'semanal',
        'monthly' => 'mensal',
    );
}

function add_occurrence_frequencies_to_js() {
    ?>
    <script type="text/javascript">
        MapasCulturais.frequencies = <?php echo json_encode(getOccurrenceFrequencies()); ?>;
    </script>
    <?php
}

function add_entity_types_to_js($entity) {
    App::i()->hook('mapasculturais.scripts', function() use($entity) {
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
        ?>
        <script type="text/javascript">
            MapasCulturais.entityTypes = MapasCulturais.entityTypes || {};
            MapasCulturais.entityTypes.<?php echo $controller->id ?> = <?php echo json_encode($types); ?>
        </script>
        <?php
    });
}

function add_taxonoy_terms_to_js($taxonomy_slug) {
    $terms = App::i()->repo('Term')->getTermsAsString($taxonomy_slug);
    App::i()->hook('mapasculturais.scripts', function() use($taxonomy_slug, $terms) {
        ?>
        <script type="text/javascript">

            MapasCulturais.taxonomyTerms = MapasCulturais.taxonomyTerms || {};
            MapasCulturais.taxonomyTerms.<?php echo $taxonomy_slug ?> = <?php echo json_encode($terms); ?>
        </script>
        <?php
    });
}

function add_agent_relations_to_js($entity){
    App::i()->hook('mapasculturais.scripts', function() use($entity) {
        ?>
        <script type="text/javascript">
            MapasCulturais.entity = MapasCulturais.entity || {};
            MapasCulturais.entity.agentRelations = <?php echo json_encode($entity->getAgentRelationsGrouped()); ?>;
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
function add_metalist_manager($object, $metalist_group, $metalist_action, $response_action, $response_target, $response_template = '', $response_transform = '', $add_description_input = false) {

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