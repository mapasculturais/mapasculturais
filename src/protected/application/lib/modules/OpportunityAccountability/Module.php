<?php

namespace OpportunityAccountability;

use DateTime;
use stdClass;
use MapasCulturais\i;
use MapasCulturais\App;
use MapasCulturais\ApiQuery;
use MapasCulturais\Entities\Project;
use MapasCulturais\Entities\ChatThread;
use MapasCulturais\Entities\ChatMessage;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Notification;
use MapasCulturais\Entities\Registration;
use OpportunityPhases\Module as PhasesModule;
use MapasCulturais\Definitions\ChatThreadType;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\EvaluationMethodConfiguration;

/**
 * @property Module $evaluationMethod
 */
class Module extends \MapasCulturais\Module
{
    public const CHAT_THREAD_TYPE = "accountability-field";
    /**
     * @var Module
     */
    protected $evaluationMethod;

    protected $inTransaction = false;

    function _init()
    {
        $app = App::i();

        $self = $this;

        $this->evaluationMethod = new EvaluationMethod($this->_config);
        $this->evaluationMethod->module = $this;

        $registration_repository = $app->repo('Registration');

         //Caso exista prestação de contas, impede que seja possível deletar fases anteriores.
         $app->hook("can(Opportunity.remove)", function($user, &$result){
            if($this->parent->accountabilityPhase ?? false){
                $result = false;
            }
        });
        
        // Abre div antes das mensagens do CHAT
        $app->hook('template(project.single.chat-messages):before ', function (){
            echo '<button ng-click="toogleTalk(field.id)">'.i::__('Abrir/Fechar conversa').'</button>';
            echo '<div class="hidden chat-{{getClassName(field.id)}}">';
        });
        
         // Fecha div depois das mensagens do CHAT
        $app->hook('template(project.single.chat-messages):end ', function (){
            echo '</div>';
        });
        

        // Adiciona Coluna na lista de prestação de Contas
        $app->hook('template(opportunity.single.registration-list-header):end', function(){
            $entity = $this->controller->requestedEntity;
            
            if($entity->isAccountabilityPhase){
                $this->part('accountability-registrations-header');
            }
        });

        // Carrega botão de publicar resultado de uma única inscrição
        $app->hook('template(opportunity.single.registration-list-item):end', function(){
            $entity = $this->controller->requestedEntity;

            if($entity->isAccountabilityPhase){
                $registrations = $entity->getAllRegistrations();
                $isPublishedResult = [];
                
                foreach ($registrations as $registration){

                    $metadata = $registration->getMetadata();

                    if(isset($metadata['isPublishedResult']) && (bool) $metadata['isPublishedResult']){
                        $isPublishedResult[] = $registration->id;
                    }
                }

                $this->jsObject['accountability']['isPublishedResult'] = $isPublishedResult;

                $this->part('accountability-registrations-list');
            }
        });

        // Adiciona resultado na tela caso metadado isPublishedResult esteja true
        $app->hook('template(opportunity.single.tabs):end', function() use ($app){
            $entity = $this->controller->requestedEntity;

            if(!$entity->publishedRegistrations && $entity->isAccountabilityPhase){           
                $registrations = $entity->getAllRegistrations();
                
                $query = new ApiQuery('MapasCulturais\\Entities\\Agent', [
                    'user' => 'EQ(@me)', 
                ]);
                    
                $ids = $query->findIds();
                
                $viewTab = false;
                foreach ($registrations as $registration){
                    if( (bool) $registration->isPublishedResult && in_array($registration->owner->id, $ids) && $registration->canUser("@control")){
                        $viewTab = true;                       
                    }
                }

                if($viewTab){
                    // Adiciona a tab Resultado
                    $this->part('accountability-published-result-tab', ['registration' => $registration]);

                    // Adiciona conteúdo na aba de resultados individual
                    $app->hook('template(opportunity.single.opportunity-registrations--tables):end', function() use ($entity){
                        $this->part('accountability-published-result-list', ['entity' => $entity]);
                    });
                }

            }
        });
       
        //Adiciona coluna de data de envio e data de avaliação na lista de pareceres
        $app->hook('template(opportunity.single.opportunity-evaluations--<<admin|committee>>--table-thead-tr):end', function(){
            $entity = $this->controller->requestedEntity;

            if($entity->isAccountabilityPhase){ 
                $this->part('accountability-evaluation-admin-table-head');
            }
        });
        
         //Adiciona conteúdo na coluna de data de envio e data de avaliação na lista de pareceres
         $app->hook('template(opportunity.single.opportunity-evaluations--<<admin|committee>>--table-tbody-tr):end', function() use ($app){
            $entity = $this->controller->requestedEntity;

            if($entity->isAccountabilityPhase){ 
                $registrations = $entity->getAllRegistrations();
                $result = [];
                foreach ($registrations as $registration){
                    $evaluation = $app->repo('RegistrationEvaluation')->findOneBy(['registration' => $registration->id]);

                    $result[$registration->id] = [
                        'dateSent' => ($registration->status >= 1) ? ($registration->sentTimestamp)->format('d/m/Y H:i:s') : i::__('Não enviado'),
                        'dateEvaluate' => ($evaluation && $evaluation->status > 0) ? ($evaluation->updateTimestamp)->format('d/m/Y H:i:s') : i::__('Não informado') 
                    ];
                }

                $this->jsObject['accountability']['dates'] = $result;

                $this->part('accountability-evaluation-admin-table-body');
            }
        });

        //Adiciona texto explicativo na tela de projetos em rascungo
        $app->hook('template(project.single.tab-about--highlighted-message):before', function(){
            $entity = $this->controller->requestedEntity;
            if($entity->isAccountability){
                if($entity->status == Project::STATUS_DRAFT){
                    $from = $entity->opportunity->accountabilityPhase->registrationFrom->format('d/m/Y');
                    $this->part('accountability-phase-project-info',['from' => $from]);
                }
            }
        });

        // Altera mensagem da revisão para informar o término e a reabertura de um paracer técnico
        $app->hook('POST(registration.saveEvaluation):before', function() use ($app){
            $request = $this->data;
            if($request['status'] == "evaluated"){
                $message = i::__("Parecer técnico finalizado");
            }else if($request['status'] == "draft"){
                $message = i::__("Parecer técnico reaberto");
            }
            $app->hook('entity(EntityRevision).insert:before',function () use ($message){
                $this->message = $message;
            });
        });

        //Evita que inscrições que um parecerisja já iniciou o parecer sejam exibidas para os demais
        $app->hook("can(Registration.evaluate)", function ($user, &$result) use ($app) {
            $registration = $this->accountabilityPhase;
            $evaluation = $app->repo("RegistrationEvaluation")->findOneBy(["registration" => $registration]);
            
            if($evaluation && !$user->equals($evaluation->user)){
                $result = false;
            }
        });

        // Adiciona no painel principal, informações da prestaao de Contas
        $app->hook('template(panel.index.content.registration):end', function() use ($app){
            $this->part('accountability/registration-accountability-panel',[]);
        });

        // Adiciona no painel principal, informações do peojeto de prestação de contas
        $app->hook('template(panel.index.content.registration):end', function() use ($app){
            $agents = new ApiQuery('MapasCulturais\\Entities\\Agent', [
                'user' => 'EQ(@me)', 
            ]);

            $projects = $app->repo('Project')->findBy(['owner' => $agents->findIds()]);
           
            $this->part('accountability/project-accountability-panel',['projects' => $projects]);
        });
        

        // impede que a fase de prestação de contas seja considerada a última fase da oportunidade
        $app->hook('entity(Opportunity).getLastCreatedPhase:params', function(Opportunity $base_opportunity, &$params) {
            $params['isAccountabilityPhase'] = 'NULL()';
        });

        // retorna a inscrição da fase de prestação de contas
        $app->hook('entity(Registration).get(accountabilityPhase)', function(&$value) use ($registration_repository){
            $opportunity = $this->opportunity->parent ?: $this->opportunity;
            $accountability_phase = $opportunity->accountabilityPhase;

            $value = $registration_repository->findOneBy([
                'opportunity' => $accountability_phase,
                'number' => $this->number
             ]);
        });

        // retorna o projeto relacionado à inscricão
        $app->hook('entity(Registration).get(project)', function(&$value) {
            if (!$value) {
                $first_phase = $this->firstPhase;
                if ($first_phase && $first_phase->id != $this->id) {
                    $value = $first_phase->project;
                }
            }
        });

        $app->hook("entity(Opportunity).validations", function (&$validations) {
            if (!($this instanceof Opportunity)) { // pula o hook unbound
                return;
            }
            if (!$this->isAccountabilityPhase) {
                return;
            }
            $key = "(\$value >= \$this->lastPhase->registrationTo)";
            $value =  i::__("A prestação de contas não pode ter início antes do término da última fase.");
            if (in_array("registrationFrom", $validations)) {
                $validations["registrationFrom"][$key] = $value;
            } else {
                $validations["registrationFrom"] = [$key => $value];
            }
            return;
        });

        // na publicação da última fase, cria os projetos
        $app->hook('entity(Opportunity).publishRegistration', function (Registration $registration) use($app) {
            if (! $this instanceof \MapasCulturais\Entities\ProjectOpportunity) {
                return;
            }
            
            if (!$this->isLastPhase) {
                return;
            }
            
            if ($registration->status != Registration::STATUS_APPROVED) {
                return;
            }

            // se não há prestação de contas
            if (!$this->firstPhase->accountabilityPhase) {
                return;
            }

            $app->disableAccessControl();

            $project = new Project;
            $project->status = 0;
            $project->type = 0;
            $project->name = $registration->projectName ?: ' ';
            $project->parent = $app->repo('Project')->find($this->ownerEntity->id);
            $project->isAccountability = true;
            $project->owner = $registration->owner;

            $first_phase = $registration->firstPhase;

            $project->registration = $first_phase;
            $project->opportunity = $this->parent ?: $this;

            $project->save();
            $first_phase->project = $project;
            $first_phase->save(true);
            
            $app->enableAccessControl();
            
            self::sendAccountabilityProjectEmail($project);
            $app->applyHookBoundTo($this, $this->getHookPrefix() . '.createdAccountabilityProject', [$project]);

        });

        $app->hook('entity(Opportunity).publishRegistrations:after', function () use ($app) {
            if (! $this instanceof \MapasCulturais\Entities\ProjectOpportunity) {
                return;
            }

            if (!$this->isLastPhase) {
                return;
            }

            // se não há prestação de contas
            if (!$this->firstPhase->accountabilityPhase) {
                return;
            }

            $module = $app->modules['OpportunityPhases'];

            $this->registerRegistrationMetadata();

            $module->importLastPhaseRegistrations($this, $this->firstPhase->accountabilityPhase, true);
        });

        // fecha os campos abertos pelo parecerista após o reenvio da prestação de contas
        $app->hook("entity(Registration).send:after", function () use ($app) {
            if (!$this->opportunity->isAccountabilityPhase) {
                return;
            }
            $app->disableAccessControl();
            $this->openFields = new stdClass();
            $this->save(true);
            $app->enableAccessControl();
            self::notifyAccountabilitySubmitted($this);
            return;
        });

        // Remove inscriçõe de prestação de contas do painel minhas inscrições 
        $app->hook("panel(registration.panel):begin", function (&$sent, &$drafts){
            foreach ($sent as $key => $registration){
                if($registration->opportunity->isAccountabilityPhase){
                    unset($sent[$key]);
                }
            }

            foreach ($drafts as $key => $registration){
                if($registration->opportunity->isAccountabilityPhase){
                    unset($drafts[$key]);
                }
            }
        });

        // Insere novo painel para mostrar as prestações de contas
        $app->hook('panel.menu:after', function() use ($app) {
            $this->part('accountability/accountability-nav-panel');
        });

        //Cria painel de prestação de contas
        $app->hook('GET(panel.accountability)', function() use($app) {
            $this->requireAuthentication();

            $this->render('accountabilitys', []);
        });

        //Filtra somente as prestações de contas para exibição no painel
        $app->hook("panel(accountability.panel):begin", function (&$sent, &$drafts){
            foreach ($sent as $key => $registration){
                if(!$registration->opportunity->isAccountabilityPhase){
                    unset($sent[$key]);
                }
            }

            foreach ($drafts as $key => $registration){
                if(!$registration->opportunity->isAccountabilityPhase){
                    unset($drafts[$key]);
                }
            }
        });
        
        $app->hook("template(project.<<single|edit>>.tabs):end", function () {
            if (Module::shouldDisplayProjectAccountabilityUI($this->controller)) {
                $this->part("accountability/project-tab");
            }
        }, 1000);

        // cria permissão project.evaluate para o projeto de prestaçao de contas
        $app->hook("can(Project.evaluate)", function ($user, &$result) use ($app) {
            if (!$this->registration->accountabilityPhase) { // we have no interest in this if it isn't an accountability project
                return;
            }
            $registration = $this->registration->accountabilityPhase;
            $evaluation = $app->repo("RegistrationEvaluation")->findOneBy(["registration" => $registration]);
            $result = $registration->canUser("evaluate", $user) && (!$evaluation || ($evaluation->status < RegistrationEvaluation::STATUS_SENT));
        });

        $app->hook("template(project.<<single|edit>>.tabs-content):end", function () {
            if (Module::shouldDisplayProjectAccountabilityUI($this->controller)) {
                $this->part("accountability/project-tab-content", [
                    "create_rule_string" => function ($occurrence) {
                        return "{$occurrence->rule->description} - {$occurrence->rule->price}";
                    }
                ]);
            }
        }, 1000);

        // aba ficha de inscrição no projeto
        $app->hook("template(project.single.tabs):end", function () {
            if (Module::shouldDisplayProjectAccountabilityUI($this->controller)) {
                $this->part("accountability/registration-tab");
            }
        });

        // conteúdo da aba ficha de inscrição no projeto
        $app->hook("template(project.single.tabs-content):end", function () {
            if (Module::shouldDisplayProjectAccountabilityUI($this->controller)) {
                $entity = $this->controller->requestedEntity;
                $this->part("accountability/registration-tab-content", ['entity' => $entity]);
            }
        });

        $app->hook("can(Registration.modify)", function ($user, &$result) use ($app) {
            if (($this->canUser("@control", $user)) && Module::hasOpenFields($this)) {
                $result = true;
            }
        });

        // barra envio da prestação de contas se o projeto não estiver publicado
        $app->hook("can(Registration.send)", function ($user, &$result) {
            $project = $this->firstPhase->project ?? null;
            if (!$project || !($this->opportunity->isAccountabilityPhase ?? false)) {
                return;
            }
            if (($project->isAccountability ?? false) && ($project->status != Project::STATUS_ENABLED)) {
                $result = false;
            }
            return;
        });

        $app->hook("PATCH(registration.single):before", function () use ($app, $self) {
            if (($this->requestedEntity->canUser("@control")) && Module::hasOpenFields($this->requestedEntity)) {
                $app->em->beginTransaction();
                $self->inTransaction = true;
                $app->hook("can(<<Agent|Space>>.<<@control|modify>>)", function ($user, &$result) {
                    $result = true;
                });
            }
        });

        $app->hook("entity(RegistrationMeta).update:before", function ($params) use ($app, $self) {
            if ($this->owner->canUser("@control")) {
                return;
            }
            $registration = $this->owner;
            if (!isset($registration->openFields)) {
                return;
            }
            $openFields = $registration->openFields;
            if (($openFields[$this->key] ?? "") == "true") {
                return;
            }
            if ($self->inTransaction) {
                $app->em->rollback();
                throw new \Exception("Permission denied!");
            }
            return;
        });

        $app->hook('slim.after', function() use ($app, $self) {
            if ($self->inTransaction) {
                $app->em->commit();
            }
        });

        // Adidiona o checkbox haverá última fase
        $app->hook('template(opportunity.edit.new-phase-form-step2):end', function () use ($app) {
            $this->part('widget-opportunity-accountability');
        });

        // Adicionar radio button para criar apenas fase de prestação de contas
        $app->hook('template(opportunity.edit.new-phase-form-step1):end', function () use ($app, $self) {
            $this->part('widget-opportunity-phase-only');
        });

        //
        $app->hook('template(opportunity.edit.new-phase-form):end', function () use ($app, $self) {
            $this->part('accountability-phase-confirmation');

        });

        //Insere part para insrir informação no editpox de crição de fases, casos seja prestação de contas
        $app->hook('template(opportunity.edit.new-phase-form):end', function () use ($app, $self) {
            $this->part('accountability-phase-info');
        });

        $app->hook('entity(Opportunity).insert:after', function () use ($app, $self) {

            $opportunityData = $app->controller('opportunity');

            if ($this->isLastPhase && isset($opportunityData->postData['hasAccountability']) && $opportunityData->postData['hasAccountability']) {
                $self->createAccountabilityPhase($this->firstPhase);
            }
        });

        $app->hook('template(project.<<single|edit>>.header-content):before', function () {
            $project = $this->controller->requestedEntity;

            if ($project->isAccountability) {
                $this->part('accountability/project-opportunity', ['opportunity' => $project->opportunity]);
            }
        });

        // Adiciona aba de projetos nas oportunidades com prestação de contas após a publicação da última fase
        $app->hook('template(opportunity.single.tabs):end', function () use ($app) {
            $entity = $this->controller->requestedEntity;
            $base_phase = $entity->parent ?? $entity;
            // accountabilityPhase existe apenas quando lastPhase existe
            if (isset($base_phase->lastPhase) && $entity->accountabilityPhase && $base_phase->lastPhase->publishedRegistrations) {
                $this->part('singles/opportunity-projects--tab', ['entity' => $entity]);
            }
        });

        $app->hook('template(opportunity.single.tabs-content):end', function () use ($app) {
            $entity = $this->controller->requestedEntity;
            $base_phase = $entity->parent ?? $entity;
            // accountabilityPhase existe apenas quando lastPhase existe
            if (isset($base_phase->lastPhase) && $entity->accountabilityPhase && $base_phase->lastPhase->publishedRegistrations) {
                $this->part('singles/opportunity-projects', ['entity' => $entity]);
            }
        });

        /**
         * adiciona o formulário do parecerista
         */
        $app->hook('template(project.single.accountability-content):end', function () use ($app) {
            $project = $this->controller->requestedEntity;
            if ($accountability = ($project->registration->accountabilityPhase ?? null)) {
                $evaluation = $app->repo('RegistrationEvaluation')->findOneBy(['registration' => $accountability]);
                $form_params = [
                    'opportunity' => $accountability->opportunity,
                    'registration' => $accountability,
                    'evaluation' => $evaluation,
                ];
                $this->jsObject['evaluation'] = $evaluation;
                if (!$evaluation || !$evaluation->canUser('modify')) {
                    return;
                }
                $this->part('accountability--evaluation-form', $form_params);
            }
        });

        // adiciona controller angular ao formulário de avaliação
        $app->hook('template(project.single.accountability-content):begin', function () use($app) {
            $project = $this->controller->requestedEntity;
            if ($accountability = $project->registration->accountabilityPhase ?? null) {
                if ($evaluation = $app->repo('RegistrationEvaluation')->findOneBy(['registration' => $accountability])) {
                    $criteria = [
                        'objectType' => RegistrationEvaluation::class,
                        'objectId' => $evaluation->id,
                        'type' => self::CHAT_THREAD_TYPE
                    ];
                    $chat_threads = $app->repo('ChatThread')->findBy($criteria);

                    $chats_grouped = [];

                    foreach($chat_threads as $thread) {
                        $chats_grouped[$thread->identifier] = $thread;
                    }

                    $this->jsObject['accountabilityChatThreads'] = (object) $chats_grouped;
                }
            }
        },-10);

        // adiciona controles de abrir e fechar chat e campo para edição
        $app->hook("template(project.single.registration-field-item):begin", function () use ($app) {
            $project = $this->controller->requestedEntity;
            $evaluation = $project->registration->accountabilityPhase ? $app->repo("RegistrationEvaluation")->findOneBy(["registration" => $project->registration->accountabilityPhase]) : null;
            if ($project->canUser("evaluate") && $evaluation && ($evaluation->status < RegistrationEvaluation::STATUS_EVALUATED)) {
                $this->part("accountability/registration-field-controls");
            }
        });

        $app->hook('template(project.single.registration-field-item):end', function () {
            echo '<div class="clearfix"></div>';
            $this->part('chat', ['thread_id' => 'getChatByField(field).id', 'closed' => '!isChatOpen(field)']);
        });

        $app->hook('template(project.single.project-event):end', function () {
            echo '<div class="clearfix"></div>';
            $this->part('chat', ['thread_id' => 'getChatByField(false).id', 'closed' => '!isChatOpen()']);
        });

        /**
         * Hook dentro  do modal de eventos
         */
        $app->hook('template(project.single.event-modal-form):begin', function (){
            echo "<input type='hidden' name='projectId' value='{$this->controller->requestedEntity->id}'>";
        });

        /**
         * Substituição dos seguintes termos
         * - avaliação por parecer
         * - avaliador por parecerista
         * - inscrição por prestação de contas
         */
        $replacements = [
            i::__('Nenhuma avaliação enviada') => i::__('Nenhum parecer técnico enviado'),
            i::__('Configuração da Avaliação') => i::__('Configuração do Parecer Técnico'),
            i::__('Comissão de Avaliação') => i::__('Comissão de Pareceristas'),
            i::__('Inscrição') => i::__('Prestacão de Contas'),
            i::__('inscrição') => i::__('prestacão de contas'),
            // inscritos deve conter somente a versão com o I maiúsculo para não quebrar o JS
            i::__('Inscritos') => i::__('Prestacoes de Contas'),
            i::__('Inscrições') => i::__('Prestações de Contas'),
            i::__('inscrições') => i::__('prestações de contas'),
            i::__('Avaliação') => i::__('Parecer Técnico'),
            i::__('avaliação') => i::__('parecer técnico'),
            i::__('Avaliações') => i::__('Pareceres'),
            i::__('avaliações') => i::__('pareceres'),
            i::__('Avaliador') => i::__('Parecerista'),
            i::__('avaliador') => i::__('parecerista'),
            i::__('Avaliadores') => i::__('Pareceristas'),
            i::__('avaliadores') => i::__('pareceristas'),
        ];

        $app->hook('view.partial(singles/opportunity-<<tabs|evaluations--admin--table|registrations--tables--manager|evaluations--committee>>):after', function($template, &$html) use($replacements) {
            $phase = $this->controller->requestedEntity;
            if ($phase->isAccountabilityPhase) {
                $html = str_replace(array_keys($replacements), array_values($replacements), $html);
            }
         });

         // Subistitui os termos (avaliações => pareceres) e (inscrições => prestações de contas)
         $app->hook('view.partial(singles/opportunity-<<evaluations--committee--table>>):after', function($template, &$html){
            $phase = $this->controller->requestedEntity;
            $terms = [
                i::__('Avaliações') => i::__('Pareceres'),
                i::__('inscrições') => i::__('prestações de contas'),
                i::__('Avaliação') => i::__('Parecer Técnico'),
                i::__('Inscrição') => i::__('Prestacão de Contas'),
                i::__('avaliação') => i::__('parecer técnico')
            ];
            
            if ($phase->isAccountabilityPhase) {
                $html = str_replace(array_keys($terms), array_values($terms), $html);
            }
         });

         // Remove status desnecessário e subistitui os termos na lista de inscrições da prestação de contas
         $app->hook('opportunity.registrationStatuses', function(&$registrationStatuses){
             if($this->isAccountabilityPhase){
                 $terms = [
                     i::__('Suplente') => i::__('Aprovada com resalvas'),
                     i::__('Selecionada') => i::__('Aprovada'),     
                     i::__('Não selecionada') => i::__('Não aprovada'),             
                    ];
                    
                    foreach($registrationStatuses as $key => $status){
                        if(!in_array($status['value'], [0,1,3,8,9,10]) || ($status['value'] == "" && !is_int($status['value']))){
                        unset($registrationStatuses[$key]);
                    }else{
                        $registrationStatuses[$key]['label'] = str_replace(array_keys($terms), array_values($terms), $status['label']);
                    }
                }
            }

            rsort($registrationStatuses);
            
            $registrationStatuses = array_reverse($registrationStatuses);
          });

         // substitui botões de importar inscrições da fase anterior
         $app->hook('view.partial(import-last-phase-button).params', function ($data, &$template) {
            $opportunity = $this->controller->requestedEntity;

            if ($opportunity->isAccountabilityPhase) {
                $template = "accountability/import-last-phase-button";
            }
         });

         // cria a avaliação se é um parecerista visitando pela primeira vez o projeto
         $app->hook('GET(project.single):before', function() use($app) {
            $project = $this->requestedEntity;
            if ($project && $project->isAccountability && $project->canUser('evaluate')) {
                if ($accountability = $project->registration->accountabilityPhase ?? null) {
                    $criteria = [
                        'registration' => $accountability
                    ];
                    if (!$app->repo('RegistrationEvaluation')->findOneBy($criteria)) {
                        $evaluation = new RegistrationEvaluation;
                        $evaluation->user = $app->user;
                        $evaluation->registration = $accountability;
                        $evaluation->save(true);
                    }
                }
            }
         });

         // redireciona a ficha de inscrição da fase de prestação de contas para o projeto relacionado
         $app->hook('GET(registration.view):before', function() use($app) {
            $registration = $this->requestedEntity;
            if ($registration->opportunity->isAccountabilityPhase) {
                if ($project = $registration->project) {
                    $app->redirect($project->singleUrl . '#/tab=accountability');
                }
            }
         });

        // fecha os campos e os chats ao enviar o parecer
        $app->hook("entity(RegistrationEvaluation).update:before", function ($params) use ($app) {
             if (($this->status == RegistrationEvaluation::STATUS_EVALUATED) &&
                $this->registration->opportunity->isAccountabilityPhase) {
                $this->registration->openFields = new stdClass();
                $criteria = [
                    "objectType" => RegistrationEvaluation::class,
                    "objectId" => $this->id,
                    "type" => self::CHAT_THREAD_TYPE
                ];
                foreach ($app->repo("ChatThread")->findBy($criteria) as $thread) {
                    $thread->status = ChatThread::STATUS_DISABLED;
                }
            }
             return;
         });

        $app->hook("POST(chatThread.createAccountabilityField)", function () use ($app) {
            $this->requireAuthentication();
            $evaluation_id = $this->data["evaluation"];
            $evaluation = $app->repo("RegistrationEvaluation")->find($evaluation_id);
            if ($evaluation->ownerUser->id != $app->user->id) {
                $this->errorJson("The user {$app->user->id} is not authorized to create a chat thread in this context.");
            }
            if ($app->repo("ChatThread")->findOneBy([
                "objectId" => $evaluation->id,
                "objectType" => $evaluation->getClassName(),
                "identifier" => $this->data["identifier"],
                "type" => self::CHAT_THREAD_TYPE]) !== null) {
                    $this->errorJson("An entity with the same specification already exists.");
            }
            $description = sprintf(i::__("Prestação de contas número %s"), $evaluation->registration->number);
            $thread = new ChatThread($evaluation, $this->data["identifier"], self::CHAT_THREAD_TYPE, $description);
            $thread->save(true);
            $app->disableAccessControl();
            $thread->createAgentRelation($evaluation->registration->owner, "participant");
            $app->enableAccessControl();
            $this->json($thread);
         });

         $app->hook('entity(Registration).get(project)', function(&$value, $metadata_key) use($app) {

            if(!$value && $this->previousPhase) {
                $this->previousPhase->registerFieldsMetadata();

                $cache_id = "registration:{$this->number}:$metadata_key";
                if($app->cache->contains($cache_id)) {
                    $value = $app->cache->fetch($cache_id);
                } else {
                    $value = $this->previousPhase->$metadata_key;
                    $app->cache->save($cache_id, $value, DAY_IN_SECONDS);
                }
            }
        });

         // Chamar o hook para criação de fase de prestação de contas
        $app->hook('module(OpportunityPhases).createNextPhase(accountability):before', function ($evaluation_method) use ($app) {

            $this->name = i::__('Prestação de Contas');
            $this->shortDescription = i::__('Descrição da Prestação de Contas');
            $this->isAccountabilityPhase = true;
            
            $first_phase = $this->firstPhase;
            $last_phase = $first_phase->lastCreatedPhase;

            $last_phase->isLastPhase = true;
            $last_phase->save();
        });

        $app->hook('module(OpportunityPhases).createNextPhase(accountability):after', function ($evaluation_method) use ($app) {
            $first_phase = $this->firstPhase;
            $first_phase->accountabilityPhase = $this;
            $first_phase->save();

        });

    }

    function register()
    {
        $app = App::i();
        $opportunity_repository = $app->repo('Opportunity');
        $registration_repository = $app->repo('Registration');
        $project_repository = $app->repo('Project');

        $app->registerController('accountability', Controller::class);

        $this->registerProjectMetadata('isAccountability', [
            'label' => i::__('Indica que o projeto é vinculado à uma inscrição aprovada numa oportunidade'),
            'type' => 'boolean',
            'default' => false
        ]);

        $this->registerProjectMetadata('opportunity', [
            'label' => i::__('Oportunidade da prestação de contas vinculada a este projeto'),
            'type' => 'Opportunity',
            'serialize' => function (Opportunity $opportunity) {
                return $opportunity->id;
            },
            'unserialize' => function ($opportunity_id) use($opportunity_repository, $app) {

                if ($opportunity_id) {
                    return $opportunity_repository->find($opportunity_id);
                } else {
                    return null;
                }
            }
        ]);

        $this->registerProjectMetadata('registration', [
            'label' => i::__('Inscrição da oportunidade da prestação de contas vinculada a este projeto (primeira fase)'),
            'type' => 'Registration',
            'serialize' => function (Registration $registration) {
                return $registration->id;
            },
            'unserialize' => function ($registration_id) use($registration_repository) {
                if ($registration_id) {
                    return $registration_repository->find($registration_id);
                } else {
                    return null;
                }
            }
        ]);

        $this->registerRegistrationMetadata('project', [
            'label' => i::__('Projeto da prestação de contas vinculada a esta inscrição (primeira fase)'),
            'type' => 'Project',
            'serialize' => function (Project $project) {
                return $project->id;
            },
            'unserialize' => function ($project_id) use($project_repository) {
                if ($project_id) {
                    return $project_repository->find($project_id);
                } else {
                    return null;
                }
            }
        ]);
            
        $this->registerRegistrationMetadata('isPublishedResult', [
            'label' => i::__('Indica se o resultado foi publicado a e uma determinada inscrição'),
            'type' => 'boolean',
            'default' => false
        ]);

        $this->registerRegistrationMetadata('openFields', [
            'label' => i::__('Campos abertos para o proponente preencher após o envio da inscrição'),
            'type' => 'json',
            'private' => true,
            'default_value' => '{}',
            'serialize' => function($value, $registration){
                $registration->checkPermission('evaluate');
                return json_encode($value);
            }
        ]);


        $this->registerOpportunityMetadata('isAccountabilityPhase', [
            'label' => i::__('Indica se a oportunidade é uma fase de prestação de contas'),
            'type' => 'boolean',
            'default' => false
        ]);

        $this->registerOpportunityMetadata('accountabilityPhase', [
            'label' => i::__('Indica se a oportunidade é uma fase de prestação de contas'),
            'type' => 'Opportunity',
            'serialize' => function (Opportunity $opportunity) {
                return $opportunity->id;
            },
            'unserialize' => function ($opportunity_id) use($opportunity_repository) {
                if ($opportunity_id) {
                    return $opportunity_repository->find($opportunity_id);
                } else {
                    return null;
                }
            }
        ]);

        $thread_type_description = i::__('Conversação entre proponente e parecerista no campo da prestação de contas');
        $definition = new ChatThreadType(self::CHAT_THREAD_TYPE, $thread_type_description, function (ChatMessage $message) {
            $thread = $message->thread;
            $evaluation = $thread->ownerEntity;
            $registration = $evaluation->registration;
            $notification_content = '';
            $sender = '';
            $recipient = '';
            $notification = new Notification;
            if ($message->thread->checkUserRole($message->user, 'admin')) {
                // mensagem do parecerista
                $notification->user = $registration->owner->user;
                $notification_content = i::__("Nova mensagem do parecerista da prestação de contas número %s");
                $sender = 'admin';
                $recipient = 'participant';
            } else {
                // mensagem do usuário responsável pela prestação de contas
                $notification->user = $evaluation->user;
                $notification_content = i::__("Nova mensagem na prestação de contas número %s");
                $sender = 'participant';
                $recipient = 'admin';
            }
            $notification->message = sprintf($notification_content, "<a href=\"{$registration->singleUrl}\" >{$registration->number}</a>");
            $notification->save(true);
            $this->sendEmailForNotification($message, $notification, $sender, $recipient);
        });
        $app->registerChatThreadType($definition);

        $this->evaluationMethod->register();
    }

    static function hasOpenFields($registration)
    {
        $openFields = $registration->openFields;
        foreach (($openFields ?? []) as $value) {
            if ($value == "true") {
                return true;
            }
        }
        return false;
    }

    // moved from send-button.php
    static function fullTextDate($timestamp)
    {
        $app = App::i();
        /* translators: gets full date in the format "26 de {January} de 2015 às 17:00" and uses App translation to replace english month name inside curly brackets to the equivalent in portuguese. It avoids requiring the operating system to have portuguese locale as used in this example: http://pt.stackoverflow.com/a/21642 */
        $date_tmp = strftime(i::__("%d de {%B} de %G às %H:%M"), $timestamp);
        return preg_replace_callback("/{(.*?)}/", function ($matches) use ($app) {
            return strtolower($app::txt(str_replace(["{", "}"], ["", ""], $matches[0]))); // removes curly brackets from the matched pattern and convert its content to lowercase
        }, $date_tmp);
    }

    static function notifyAccountabilitySubmitted($registration)
    {
        $app = App::i();
        $evaluation = $app->repo("RegistrationEvaluation")->findOneBy(["registration" => $registration]);
        if (!$evaluation) {
            return;
        }
        $notification_content = i::__("Prestação de contas número %s re-enviada");
        $notification = new Notification;
        $notification->user = $evaluation->user;
        $notification->message = sprintf($notification_content, "<a href=\"{$registration->singleUrl}\" >{$registration->number}</a>");
        $notification->save(true);
        return;
    }

    static function sendAccountabilityProjectEmail(Project $project)
    {
        $app = App::i();
        $template = "accountability/project-communication.html";
        $phase = $project->opportunity->accountabilityPhase;
        $start = self::fullTextDate($phase->registrationFrom->getTimestamp());
        $end = self::fullTextDate($phase->registrationTo->getTimestamp());
        $params = [
            "siteName" => $app->view->dict("site: name", false),
            "user" => $project->owner->name,
            "baseUrl" => $app->getBaseUrl(),
            "opportunityTitle" => $project->opportunity->name,
            "projectUrl" => $project->getEditUrl(),
            "accountabilityStartDate" => $start,
            "accountabilityEndDate" => $end
        ];
        $email_params = [
            "from" => $app->config["mailer.from"],
            "to" => ($project->owner->emailPrivado ??
                     $project->owner->emailPublico ??
                     $project->ownerUser->email),
            "subject" => sprintf(i::__("Novo projeto criado no %s"),
                                 $params["siteName"]),
            "body" => $app->renderMustacheTemplate($template, $params)
        ];
        if (!isset($email_params["to"])) {
            return;
        }
        $app->createAndSendMailMessage($email_params);
        return;
    }

    static function shouldDisplayProjectAccountabilityUI($controller)
    {
        $project = $controller->requestedEntity;
        if ($project->isAccountability) {
            if ($project->canUser("@control") || $project->canUser("evaluate") || $project->opportunity->canUser("@control")) {
                return ($project->status > Project::STATUS_DRAFT);
            }
        }
        return false;
    }

    // Migrar essa função para o módulo "Opportunity phase"
    function createAccountabilityPhase(Opportunity $parent)
    {
        $app = App::i();

        $opportunity_class_name = $parent->getSpecializedClassName();

        $last_phase = \OpportunityPhases\Module::getLastCreatedPhase($parent);

        $phase = new $opportunity_class_name;

        $phase->status = Opportunity::STATUS_DRAFT;
        $phase->parent = $parent;
        $phase->ownerEntity = $parent->ownerEntity;

        $phase->name = i::__('Prestação de Contas');
        $phase->registrationCategories = $parent->registrationCategories;
        $phase->shortDescription = i::__('Descrição da Prestação de Contas');
        $phase->type = $parent->type;
        $phase->owner = $parent->owner;
        $phase->useRegistrations = true;
        $phase->isOpportunityPhase = true;
        $phase->isAccountabilityPhase = true;

        $_from = $last_phase->registrationTo ? clone $last_phase->registrationTo : new \DateTime;
        $_to = $last_phase->registrationTo ? clone $last_phase->registrationTo : new \DateTime;
        $_to->add(date_interval_create_from_date_string('1 days'));

        $phase->registrationFrom = $_from;
        $phase->registrationTo = $_to;

        $phase->save(true);

        $parent->accountabilityPhase = $phase;
        $parent->save(true);

        $app->disableAccessControl();
        $evaluation_method_configuration = new EvaluationMethodConfiguration;
        $evaluation_method_configuration->opportunity = $phase;
        $evaluation_method_configuration->type = 'accountability';
        $evaluation_method_configuration->save(true);
        $app->disableAccessControl();
    }
}
