<?php
namespace ProjectPhases;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions;


class Plugin extends \MapasCulturais\Plugin{
    
    function getPhases($project){
        $app = App::i();
        $phases = $app->repo('Project')->findBy([
            'parent' => $project,
            'status' => -1
        ]);
        
        return $phases;
    }
    
    function _init () {
        $app = App::i();
        
        $app->view->enqueueScript('app', 'plugin-project-phases', 'js/project-phases.js', ['mapasculturais']);
        
        $plugin = $this;
        
        $app->hook('GET(project.createNextPhase)', function() use($app, $plugin){
            $parent = $this->requestedEntity;
            
            $_phases = [
                $app->txt('Segunda fase'),
                $app->txt('Terceira fase'),
                $app->txt('Quarta fase'),
                $app->txt('Quinta fase'),
                $app->txt('Sexta fase'),
                $app->txt('Sétima fase'),
                $app->txt('Oitava fase'),
                $app->txt('Nona fase'),
                $app->txt('Décima fase')
            ];
            
            $phases = $plugin->getPhases($parent);
            
            $num_phases = count($phases);
            
            $phase = new Entities\Project;
            $phase->status = -1;
            $phase->parent = $parent;
            $phase->name = $_phases[$num_phases];
            $phase->shortDescription = 'Descrição da ' . $_phases[$num_phases];
            $phase->type = $parent->type;
            $phase->owner = $parent->owner;
            $phase->useRegistrations = true;
            $phase->isProjectPhase = true;

            $phase->save(true);

            $app->redirect($phase->editUrl);
        });
        
        $app->hook('view.partial(singles/widget-projects).params', function(&$params, &$template) use ($app, $plugin){
            if($this->controller->action === 'create'){
                return;
            }
            
            $project = $this->controller->requestedEntity;
            
            if($project->isProjectPhase){
                $template = 'empty';
            }

            $params['projects'] = array_filter($params['projects'], function($e){
                if(! (bool) $e->isProjectPhase){
                    return $e;
                }
            });
            
            if($project->isProjectPhase){
                $project = $project->parent;
            }

            if(!$project->useRegistrations || !$project->canUser('@controll')){
                return;
            }
            
            $phases = $plugin->getPhases($project);
            
            $app->view->part('widget-project-phases', ['project' => $project, 'phases' => $phases]);
        });

        $app->hook('view.partial(<<singles/type|entity-parent>>).params', function(&$data, &$template){
            $project = $this->controller->requestedEntity;
            
            if($project->isProjectPhase){
                $data['disable_editable'] = true;
            }
        });
        
        // remove a aba agenda de um projeto que é uma fase de outro projeto
        $app->hook('view.partial(<<agenda|singles/project-events>>).params', function(&$data, &$template){
            
            $project = $this->controller->requestedEntity;
            
            if($project->isProjectPhase){
                $template = 'empty';
            }
        });
        
        // faz com que a fase seja acessível mes
        $app->hook('entity(Project).canUser(view)', function($user, &$result){
            if($this->isProjectPhase && $this->status === -1){
                $result = true;
            }
        });
    }
    
    function register () {
        
        $app = App::i();

        $def = new Definitions\Metadata('isProjectPhase', [
            'label' => $app->txt('Is a project phase?')
        ]);

        $app->registerMetadata($def, 'MapasCulturais\Entities\Project');
    }
    
}