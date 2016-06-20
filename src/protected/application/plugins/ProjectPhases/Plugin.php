<?php
namespace ProjectPhases;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions;


class Plugin extends \MapasCulturais\Plugin{
    
    static function getBaseProject(){
        $project = self::getRequestedProject();
        
        if(!$project){
            return null;
        }
        
        if($project->isProjectPhase){
            $project = $project->parent;
        }
        
        return $project;
    }
    
    static function getRequestedProject(){
        $app = App::i();
        
        $project = $app->view->controller->requestedEntity;
        
        if(!$project){
            return null;
        }
        
        return $project;
    }
    
    static function getLastPhase(Entities\Project $base_project){
        $app = App::i();
        
        if ($base_project->canUser('@control')) {
            $status = [0,-1];
        } else {
            $status = -1;
        }
        
        $result = $app->repo('Project')->findOneBy([
            'parent' => $base_project,
            'status' => $status
        ],['registrationTo' => 'DESC', 'id' => 'DESC']);
        
        return $result ? $result : $base_project;
    }
    
    /**
     * Retorna a última fase que teve seu período de inscrição terminado
     * @param \MapasCulturais\Entities\Project $base_project
     * @return \MapasCulturais\Entities\Project 
     */
    static function getLastCompletedPhase(Entities\Project $base_project){
        $now = new \DateTime;
        
        if($base_project->registrationTo > $now){
            return null;
        }
        
        $result = $base_project;
        $phases = self::getPhases($base_project);
        
        foreach($phases as $phase){
            if($phase->registrationTo <= $now){
                $result = $phase;
            }
        }
        
        return $result;
    }
    
    /**
     * Retorna a fase atual
     * @param \MapasCulturais\Entities\Project $base_project
     * @return \MapasCulturais\Entities\Project 
     */
    static function getCurrentPhase(Entities\Project $base_project){
        $now = new \DateTime;
        
        $result = $base_project;
        $phases = self::getPhases($base_project);
        
        foreach($phases as $phase){
            if($phase->registrationTo > $now){
                continue;
            }
            $result = $phase;
        }
        
        return $result;
    }
    
    static function canCreatePhases(Entities\Project $project){
        return $project->useRegistrations && $project->registrationTo;
    }
    
    static function getPhases($project){
        if ($project->canUser('@control')) {
            $status = [0,-1];
        } else {
            $status = -1;
        }
        
        $app = App::i();
        $phases = $app->repo('Project')->findBy([
            'parent' => $project,
            'status' => $status
        ],['registrationTo' => 'ASC', 'id' => 'ASC']);
        
        return $phases;
    }
    
    function _init () {
        $app = App::i();
        
        $app->view->enqueueScript('app', 'plugin-project-phases', 'js/project-phases.js', ['mapasculturais']);
        $app->view->enqueueStyle('app', 'plugin-project-phases', 'css/project-phases.css');
        
        // action para importar as inscrições da última fase concluida
        $app->hook('GET(project.importLastPhaseRegistrations)', function() use($app) {
            $base_project = self::getBaseProject();
            $phase = self::getCurrentPhase($base_project);
            
            $phase->checkPermission('@control');
            
            $last_phase = self::getLastCompletedPhase($base_project);
            
            $registrations = $last_phase->getSentRegistrations();
            
            die(var_dump($last_phase->name, count($registrations)));
        });
        
        // action para criar uma nova fase no projeto
        $app->hook('GET(project.createNextPhase)', function() use($app){
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
            
            $phases = self::getPhases($parent);
            
            $num_phases = count($phases);
            
            $phase = new Entities\Project;
            $phase->status = Entities\Project::STATUS_DRAFT;
            $phase->parent = $parent;
            $phase->name = $_phases[$num_phases];
            $phase->shortDescription = 'Descrição da ' . $_phases[$num_phases];
            $phase->type = $parent->type;
            $phase->owner = $parent->owner;
            $phase->useRegistrations = true;
            $phase->isProjectPhase = true;
            
            $last_phase = self::getLastPhase($parent);
            
            $_from = clone $last_phase->registrationTo;
            $_to = clone $last_phase->registrationTo;
            $_to->add(date_interval_create_from_date_string('1 days'));
            
            $phase->registrationFrom = $_from;
            $phase->registrationTo = $_to;
            

            $phase->save(true);

            $app->redirect($phase->editUrl);
        });
        
        // redireciona para a página do projeto após deletar uma fase
        $app->hook('DELETE(project):beforeRedirect', function($entity, &$redirect_url){
            if($entity->isProjectPhase){
                $redirect_url = $entity->parent->singleUrl;
            }
        });
        
        // desliga a edição do campo principal de data quando vendo uma fase
        $app->hook('view.partial(singles/project-about--registration-dates).params', function(&$params){
            $project = self::getRequestedProject();
            $base_project = self::getBaseProject();
            
            if(!$project) {
                return;
            }
            
            if($project->isProjectPhase){
                $params['entity'] = $base_project;
                $params['disable_editable'] = true;
            }
        });
        
        // subsitui a mensagem de projeto rascunho quando for uma fase de projeto
        $app->hook('view.partial(singles/entity-status).params', function(&$params, &$template_name){
            $project = self::getRequestedProject();
            
            if(!$project){
                return;
            }
            
            if($project->isProjectPhase){
                $template_name = 'project-phase-status';
            }
        });
        
        // muda o status de publicação dos projetos
        $app->hook('view.partial(singles/control--edit-buttons).params', function(&$params) use ($app){
            $project = self::getRequestedProject();
            
            if(!$project){
                return;
            }
            
            if($project->isProjectPhase){
                $params['status_enabled'] = -1;
            }
        });
        
        // adiciona a lista e botão para criar novas fases
        $app->hook('view.partial(singles/widget-projects).params', function(&$params, &$template) use ($app){
            if($this->controller->action === 'create'){
                return;
            }
            
            $project = self::getRequestedProject();
            
            if($project->isProjectPhase){
                $template = 'empty';
                return;
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
            
        });
        
        // remove opção de desativar inscrições online nas fases
        $app->hook('view.partial(singles/project-about--online-registration-button).params', function(&$data, &$template){ 
            $project = self::getRequestedProject();
            
            if(!$project){
                return;
            }
            
            if($project->isProjectPhase){
                echo '<br>';
                $template = 'empty';
            }
        });
        
        // adiciona a lista de fases e o botão 'adicionar fase'
        $app->hook('template(project.<<single|edit>>.tab-about--highlighted-message):end', function() use($app){
            $project = self::getBaseProject();
            
            if(!self::canCreatePhases($project)){
                return;
            }
            
            $phases = self::getPhases($project);
            
            $app->view->part('widget-project-phases', ['project' => $project, 'phases' => $phases]);
        });


        // desabilita o modo de edição das partes abaixo
        $app->hook('view.partial(<<singles/type|entity-parent>>).params', function(&$data, &$template){
            $project = $this->controller->requestedEntity;
            
            if(!$project){
                return;
            }
            
            if($project->isProjectPhase){
                $data['disable_editable'] = true;
            }
        });
        
        // remove a aba agenda de um projeto que é uma fase de outro projeto
        $app->hook('view.partial(<<agenda|singles/project-events>>).params', function(&$data, &$template){
            $project = $this->controller->requestedEntity;
            
            if(!$project){
                return;
            }
            
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