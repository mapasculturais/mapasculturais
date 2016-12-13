<?php
namespace ProjectPhases;

use MapasCulturais\App,
    MapasCulturais\Entities,
    MapasCulturais\Definitions,
    MapasCulturais\Exceptions;


class Plugin extends \MapasCulturais\Plugin{
    
    /**
     * Retorna o projeto principal
     * 
     * @return \MapasCulturais\Entities\Project
     */
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
    
    /**
     * Retorna o projeto/fase que está sendo visualizado
     * 
     * @return \MapasCulturais\Entities\Project
     */
    static function getRequestedProject(){
        $app = App::i();
        
        $project = $app->view->controller->requestedEntity;
        
        if(!$project){
            return null;
        }
        
        return $project;
    }
    
    /**
     * Retorna a última fase do projeto
     * 
     * @param \MapasCulturais\Entities\Project $base_project
     * @return \MapasCulturais\Entities\Project
     */
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
    
    /**
     * Retorna a fase anterior a fase informada
     * 
     * @param \MapasCulturais\Entities\Project $phase
     * @return \MapasCulturais\Entities\Project a fase anterior
     */
    static function getPreviousPhase(Entities\Project $phase){
        if (!$phase->isProjectPhase) { 
            return null;
        }
        
        $base_project = self::getBaseProject();
        
        $phases = self::getPhases($base_project);
        
        $result = $base_project;
        
        foreach($phases as $p){
            if($p->registrationTo < $phase->registrationTo){
                $result = $p;
            }
        }
        
        return $result;
    }
    
    
    /**
     * Retorna as fases do projeto informado
     * 
     * @param \MapasCulturais\Entities\Project $project
     * @return \MapasCulturais\Entities\Project[]
     */
    static function getPhases(Entities\Project $project){
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
        
        $phases = array_filter($phases, function($item) { 
            if($item->isProjectPhase){
                return $item;
            }
        });
        
        return $phases;
    }
    
    /**
     * O projeto informado tem os requisitos mínimos para se criar novas fases?
     * 
     * @param \MapasCulturais\Entities\Project $project
     * @return type
     */
    static function canCreatePhases(Entities\Project $project){
        return $project->useRegistrations && $project->registrationTo;
    }
    
    function _init () {
        $app = App::i();
        
        $app->view->enqueueStyle('app', 'plugin-project-phases', 'css/project-phases.css');
        
        // action para criar uma nova fase no projeto
        $app->hook('GET(project.createNextPhase)', function() use($app){
            $parent = $this->requestedEntity;
            
            $_phases = [
                \MapasCulturais\i::__('Segunda fase'),
                \MapasCulturais\i::__('Terceira fase'),
                \MapasCulturais\i::__('Quarta fase'),
                \MapasCulturais\i::__('Quinta fase'),
                \MapasCulturais\i::__('Sexta fase'),
                \MapasCulturais\i::__('Sétima fase'),
                \MapasCulturais\i::__('Oitava fase'),
                \MapasCulturais\i::__('Nona fase'),
                \MapasCulturais\i::__('Décima fase')
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
        
        // adiciona o botão de importar inscrições da fase anterior
        $app->hook('view.partial(singles/project-registrations--tables--manager):before', function(){
            if($this->controller->action === 'create'){
                return;
            }
            
            $project = $this->controller->requestedEntity;
        
            if($project->isProjectPhase){
                $this->part('import-last-phase-button', ['entity' => $project]);
            }
        });
        
        // adiciona na ficha de inscrição das fases o link para a inscrição anterior
        $app->hook('view.partial(singles/registration-<<edit|single>>--header):before', function() use($app){
            $registration = $this->controller->requestedEntity;
            if($prev_id = $registration->previousPhaseRegistrationId){
                $previous_phase_registration = $app->repo('Registration')->find($prev_id);
                $this->part('previous-phase-registration-link', ['previous_phase_registration' => $previous_phase_registration, 'registration' => $registration]);
            }
            
            if($next_id = $registration->nextPhaseRegistrationId){
                $next_phase_registration = $app->repo('Registration')->find($next_id);
                $this->part('next-phase-registration-link', ['next_phase_registration' => $next_phase_registration, 'registration' => $registration]);
            }
        });

        // action para importar as inscrições da última fase concluida
        $app->hook('GET(project.importLastPhaseRegistrations)', function() use($app) {
            $target_project = self::getRequestedProject();
            
            $target_project ->checkPermission('@control');
            
            if($target_project->previousPhaseRegistrationsImported){
                $this->errorJson(\MapasCulturais\i::__('As inscrições já foram importadas para esta fase'), 400);
            }
            
            $previous_phase = self::getPreviousPhase($target_project);
            
            $registrations = array_filter($previous_phase->getSentRegistrations(), function($item){
                if($item->status === Entities\Registration::STATUS_APPROVED){
                    return $item;
                }
            });
            
            if(count($registrations) < 1){
                $this->errorJson(\MapasCulturais\i::__('Não há inscrições aprovadas ou suplentes na fase anterior'), 400);
            }
            
            $new_registrations = [];
            
            $app->disableAccessControl();
            foreach ($registrations as $r){
                $reg = new Entities\Registration;
                $reg->owner = $r->owner;
                $reg->project = $target_project;
                $reg->status = Entities\Registration::STATUS_DRAFT;
                $reg->previousPhaseRegistrationId = $r->id;
                $reg->save(true);
                
                $r->nextPhaseRegistrationId = $reg->id;
                $r->save(true);
                
                $new_registrations[] = $reg;
            }
            
            $target_project->previousPhaseRegistrationsImported = true;
            
            $target_project->save(true);
            
            $app->enableAccessControl();
            
            $this->finish($new_registrations);
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
        
        // remove form de fazer inscrição das fases
        $app->hook('view.partial(singles/project-registrations--form).params', function(&$data, &$template){
            $project = self::getRequestedProject();
            
            if(!$project){
                return;
            }
            
            if($project->isProjectPhase){
                echo '<br>';
                $template = 'empty';
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
        
        $app->hook('POST(registration.index):before', function() use($app) {
            $project = $app->repo('Project')->find($this->data['projectId']);
            
            if($project->isProjectPhase){
                throw new Exceptions\PermissionDenied($app->user, $project, 'register');
            }
        });
    }
    
    
    function register () {
        $app = App::i();

        $def__is_project_phase = new Definitions\Metadata('isProjectPhase', ['label' => \MapasCulturais\i::__('Is a project phase?')]);
        $def__previous_phase_imported = new Definitions\Metadata('previousPhaseRegistrationsImported', ['label' => \MapasCulturais\i::__('Previous phase registrations imported')]);

        $app->registerMetadata($def__is_project_phase, 'MapasCulturais\Entities\Project');
        $app->registerMetadata($def__previous_phase_imported, 'MapasCulturais\Entities\Project');
        
        $def__prev = new Definitions\Metadata('previousPhaseRegistrationId', ['label' => \MapasCulturais\i::__('Previous phase registration id')]);
        $def__next = new Definitions\Metadata('nextPhaseRegistrationId', ['label' => \MapasCulturais\i::__('Next phase registration id')]);

        $app->registerMetadata($def__prev, 'MapasCulturais\Entities\Registration');
        $app->registerMetadata($def__next, 'MapasCulturais\Entities\Registration');
    }
    
}
