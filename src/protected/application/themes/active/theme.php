<?php
use MapasCulturais\App;
use MapasCulturais\Entities;
use MapasCulturais\Entities\Notification;

$app = App::i();


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



// mudei o caminho de MapasCulturais.Editables.entity para MapasCulturais.entity.definition

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
