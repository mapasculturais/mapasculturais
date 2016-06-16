<?php
namespace ProjectPhases;

use MapasCulturais\App;


class Plugin extends \MapasCulturais\Plugin{
    
    function _init () {
        $app = App::i();
        
        $app->hook('GET(project.createNextPhase)', function() use($app){
            $parent = $this->requestedEntity;

            $phase = new MapasCulturais\Entities\Project;
            $phase->parent = $parent;
            $phase->name = "Nova fase";
            $phase->type = $parent->type;
            $phase->owner = $parent->owner;

            $phase->save(true);

            $app->redirect($phase->editUrl);
        });
        
        $app->hook('view.partial(singles/widget-projects).params', function(&$params){

            $params['projects'] = array_filter($params['projects'], function($e){
                if(! (bool) $e->isProjectPhase){
                    return $e;
                }
            });
        });

        $app->hook('view.partial(downloads):before', function() use ($app){
            $project = $this->controller->requestedEntity;

            if(!$project->useRegistrations){
                return;
            }
            
            $app->view->part('widget-project-phases', ['project' => $project]);
        });
        
        $app->hook('view.render(project/<<edit|create|single>>):before', function(){
 
           ?>
            <script> 
                document.onready = function(){
                    $('#agenda').remove();
                    $('#tab-agenda').parent().remove();
                }
            </script>
            <?php 
        });

        $app->hook('entity(Project).canUser(view)', function($user, &$result){
            if($this->isProjectPhase && $this->status === -1){
                $result = true;
            }
        });
    }
    
    function _register () {
        $app = App::i();

        $def = new MapasCulturais\Definitions\Metadata('isProjectPhase', [
            'label' => $app->txt('Is a project phase?')
        ]);

        $app->registerMetadata($def, 'MapasCulturais\Entities\Project');
    }
    
    function register() {
        
    }
}