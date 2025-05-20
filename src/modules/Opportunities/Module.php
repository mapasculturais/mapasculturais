<?php

namespace Opportunities;

use DateTime;
use Exception;
use MapasCulturais\App;
use MapasCulturais\Controllers\Opportunity as ControllersOpportunity;
use MapasCulturais\Entities\AgentRelation;
use MapasCulturais\i;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;

class Module extends \MapasCulturais\Module{

    function __construct(array $config = [])
    {
        $app = App::i();
        parent::__construct($config);
    }

    function _init(){

        /** @var App $app */
        $app = App::i();

        // Registro de Jobs
        $app->registerJobType(new Jobs\StartEvaluationPhase(Jobs\StartEvaluationPhase::SLUG));
        $app->registerJobType(new Jobs\StartDataCollectionPhase(Jobs\StartDataCollectionPhase::SLUG));
        $app->registerJobType(new Jobs\FinishEvaluationPhase(Jobs\FinishEvaluationPhase::SLUG));
        $app->registerJobType(new Jobs\FinishDataCollectionPhase(Jobs\FinishDataCollectionPhase::SLUG));
        $app->registerJobType(new Jobs\PublishResult(Jobs\PublishResult::SLUG));
        $app->registerJobType(new Jobs\UpdateSummaryCaches(Jobs\UpdateSummaryCaches::SLUG));

        $app->registerJobType(new Jobs\RefreshViewEvaluations(Jobs\RefreshViewEvaluations::SLUG));

        // Quando a oportunidade é multifases e ocorre uma alteração na propriedade, essa mudança também se reflete nas fases subsequentes.
        $app->hook("entity(Opportunity).saveOwnerAgent", function() {
            /** @var \MapasCulturais\Entities\Opportunity $this */
            if(!$this->isNew()) {
                $phases = $this->allPhases;
                foreach($phases as $phase) {
                    $phase->owner = $this->owner;
                    $phase->save(true);
                }
            }
        });

        $app->hook('GET(<<registration>>.<<*>>):before', function() use ($app) {
            $registration = $this->requestedEntity;
            $app->hook('entity(Registration).propertiesMetadata', function(&$metadada) use ($registration) {
                $metadada['category']['field_type'] = 'select';
                $metadada['proponentType']['field_type'] = 'select';
                $metadada['range']['field_type'] = 'select';

                $opportunity = $registration->opportunity;
                if(($categories = $opportunity->registrationCategories) || true) {
                    $options = [];

                    foreach($categories as $category) {
                        $options[$category] =  $category;
                    }

                    $metadada['category']['options'] = $options;
                    $metadada['category']['optionsOrder'] = $categories;
                    $metadada['category']['required'] = true;
                }

                if(($proponentTypes = $opportunity->registrationProponentTypes) || true) {
                    $options = [];

                    foreach($proponentTypes as $proponentType) {
                        $options[$proponentType] =  $proponentType;
                    }

                    $metadada['proponentType']['options'] = $options;
                    $metadada['proponentType']['optionsOrder'] = $proponentTypes;
                }

                if($ranges = $opportunity->registrationRanges) {
                    $options = [];
                    $_ranges = [];
                    foreach($ranges as $range) {
                        $range = (object) $range;
                        $options[$range->label] =  $range->label;
                        $_ranges[] = $range->label;
                    }

                    $metadada['range']['options'] = $options;
                    $metadada['range']['optionsOrder'] = $_ranges;
                }
            });
        });

        // ajusta validação da área de interesse
        $app->hook('entity(Opportunity).validationErrors', function(&$errors) use ($app){
            /** @var Opportunity $this */
            if(isset($errors['term-area'])) {
                if($this->parent){
                    unset($errors['term-area']);
                } else {
                    foreach($errors['term-area'] as &$termError) {
                        if(strpos($termError, i::__('área de atuação')) !== false) {
                            $termError = str_replace(i::__('área de atuação'), i::__('área de interesse'), $termError);
                        }
                    }
                }
            }
        });

        // atualiza o cache dos resumos das fase de avaliação
        $app->hook("entity(Registration).<<send|insert>>:before", function() use ($app) {
            /** @var Registration $this */
            $evaluation_method_configuration = $this->opportunity->evaluationMethodConfiguration ?: null;
            $cache_key = "updateSummary::{$this->opportunity}::{$evaluation_method_configuration}";
            if(!$app->mscache->contains($cache_key)) {
                $app->mscache->save($cache_key, true, 10);
                $app->enqueueOrReplaceJob(Jobs\UpdateSummaryCaches::SLUG, [
                    'opportunity' => $this->opportunity,
                    'evaluationMethodConfiguration' => $evaluation_method_configuration,
                ], '10 seconds');
                $app->mscache->delete($cache_key);
            }
        });

        $app->hook("entity(Registration).status(<<*>>)", function() use ($app) {
            $app->log->debug("Registration {$this->id} status changed to {$this->status}");
            
            /** @var Registration $this */
            /** @var Opportunity $opportunity */
            $opportunity = $this->opportunity;
            do{
                $app->enqueueOrReplaceJob(Jobs\UpdateSummaryCaches::SLUG, [
                    'opportunity' => $opportunity
                ], '10 seconds');

                $opportunity = $opportunity->nextPhase;
                
            } while ($opportunity);
        });
        
        $app->hook("entity(RegistrationEvaluation).save:after", function() use ($app) {
            /** @var RegistrationEvaluation $this */
            $cache_key = "updateSummary::{$this->registration->opportunity->evaluationMethodConfiguration}";
            if(!$app->mscache->contains($cache_key)) {
                $app->mscache->save($cache_key, true, 10);
                $app->enqueueOrReplaceJob(Jobs\UpdateSummaryCaches::SLUG, [
                    'evaluationMethodConfiguration' => $this->registration->opportunity->evaluationMethodConfiguration
                ], '10 seconds');
                $app->mscache->delete($cache_key);
            }
            
        });

        // Método para que devolve se existe avaliações técnicas nas fases anteriores
        $app->hook("Entities\\Opportunity::hasPreviousTechnicalEvaluation", function() use ($app) {
            $previousPhases = $this->previousPhases;

            $hasPreviousTechnicalEvaluation =  false;
            foreach($previousPhases as $phase) {
                if($phase->evaluationMethodConfiguration && $phase->evaluationMethodConfiguration->type->id === "technical") {
                    $hasPreviousTechnicalEvaluation = true;
                }
            }

            return $hasPreviousTechnicalEvaluation;
        });

        $app->hook('entity(Opportunity).validations', function(&$validations) {
            /** @var Opportunity $this */
            if (!$this->isNew() && !$this->isLastPhase) {
                $validations['registrationFrom']['required'] = i::__('A data inicial das inscrições é obrigatória');
                $validations['registrationTo']['required'] = i::__('A data final das inscrições é obrigatória');
                $validations['shortDescription']['required'] = i::__('A descrição curtá é obrigatória');
            }
        });

        /**
         * Na publicação da oportunidade cria efetua o salvamento para de todas as fases
         * para que dessa maneira os jobs sejam criados.
         * 
         * @todo pensar uma maneira de ativas os jobs sem necessidade de salvar as fases
         */
        $app->hook("entity(Opportunity).<<(un)?publish|(un)?archive|(un)?delete|destroy>>:after", function() use ($app){
            /** @var Opportunity $this */

            foreach($this->allPhases as $phase) {
                $phase->scheduleJobs();

                if($phase->evaluationMethodConfiguration) {
                    $phase->evaluationMethodConfiguration->scheduleJobs();
                }
            }
        });

        $app->hook("entity(Opportunity).save:finish", function() use ($app){
            /** @var Opportunity $this */
            $this->scheduleJobs();
        });

        $app->hook("Entities\\Opportunity::scheduleJobs", function() use ($app){
            /** @var Opportunity $this */
            $data = ['opportunity' => $this];

            // verifica se a oportunidade e a fase estão públicas
            $active = in_array($this->status, [-1, Opportunity::STATUS_ENABLED]) && $this->firstPhase->status === Opportunity::STATUS_ENABLED;

            $now = new \DateTime;

            // Executa Job no momento da publicação automática dos resultados da fase
            $registration_from_changed = $this->_changes['registrationFrom'] ?? false;
            $registration_to_changed = $this->_changes['registrationTo'] ?? false;

            if($active && $this->publishTimestamp && ($this->autoPublish && $this->publishTimestamp >= $now || $registration_from_changed)){
                $app->enqueueOrReplaceJob(Jobs\PublishResult::SLUG, $data, $this->publishTimestamp->format("Y-m-d H:i:s"));
            } else {
                $app->unqueueJob(Jobs\PublishResult::SLUG, $data);
            }

            // Executa Job no início da fase de coleta de dados
            if ($active && $this->registrationFrom && $this->registrationFrom >= $now) {
                $app->enqueueOrReplaceJob(Jobs\StartDataCollectionPhase::SLUG, $data, $this->registrationFrom->format("Y-m-d H:i:s"));
            } else {
                $app->unqueueJob(Jobs\StartDataCollectionPhase::SLUG, $data);
            }

            // Executa Job no final da fase de coleta de dados
            if ($active && $this->registrationTo && ($this->registrationTo >= $now || $registration_to_changed)) {
                $app->enqueueOrReplaceJob(Jobs\FinishDataCollectionPhase::SLUG, $data, $this->registrationTo->format("Y-m-d H:i:s"));
            } else {
                $app->unqueueJob(Jobs\FinishDataCollectionPhase::SLUG, $data);
            }
        });

        
        $app->hook("entity(EvaluationMethodConfiguration).save:finish", function() use ($app){
            /** @var EvaluationMethodConfiguration $this */
            $this->scheduleJobs();
        });

        $app->hook("Entities\EvaluationMethodConfiguration::scheduleJobs", function() use ($app){
            /** @var EvaluationMethodConfiguration $this */
            $data = [
                'opportunity' => $this->opportunity,
                'phase' => $this,
            ];

            $active = in_array($this->opportunity->status, [-1, Opportunity::STATUS_ENABLED]) && $this->opportunity->firstPhase->status === Opportunity::STATUS_ENABLED;

            $now = new \DateTime;

            // Executa Job no início de fase de avaliação
            $avaluation_from_changed = $this->_changes['registrationFrom'] ?? false;
            $evaluation_to_changed = $this->_changes['registrationTo'] ?? false;

            if ($active && $this->evaluationFrom && ($this->evaluationFrom >= $now || $avaluation_from_changed)) {
                $app->enqueueOrReplaceJob(Jobs\StartEvaluationPhase::SLUG, $data, $this->evaluationFrom->format("Y-m-d H:i:s"));
            }else {
                $app->unqueueJob(Jobs\StartEvaluationPhase::SLUG, $data);
            }

            // Executa Job no início de fase de avaliação
            if ($active && $this->evaluationTo && ($this->evaluationTo >= $now || $evaluation_to_changed)) {
                $app->enqueueOrReplaceJob(Jobs\FinishEvaluationPhase::SLUG, $data, $this->evaluationTo->format("Y-m-d H:i:s"));
            }else {
                $app->unqueueJob(Jobs\FinishEvaluationPhase::SLUG, $data);

            }
        });
        
          //Cria painel de prestação de contas
        $app->hook('GET(panel.opportunities)', function() use($app) {
            $this->requireAuthentication();
            $this->render('opportunities', []);
        });

        $app->hook('GET(panel.registrations)', function() use($app) {
            $this->requireAuthentication();
            $this->render('registrations', []);
        });

        $app->hook('GET(panel.evaluations)', function() use($app) {
            $this->requireAuthentication();
            $this->render('evaluations', []);
        });

        $app->hook('GET(registration.registrationPrint)', function() use($app) {
            $this->requireAuthentication();

            $this->entityClassName = "MapasCulturais\\Entities\\Registration";
            
            $this->layout = "registration";

            $entity = $this->requestedEntity;
            
            $this->layout = 'print-layout';

            $this->render("registration-print", ['entity' => $entity]);
        });

        // Atualiza o resumo de avaliações dos avaliadores do edital
         $app->hook("GET(opportunity.updateSumaryEvaluation)", function() use ($app) {
            /** @var ControllersOpportunity $this */
            $app = App::i();
        
            $this->requireAuthentication();

            $entity = $this->requestedEntity;

            if(!$entity) {
                $app->pass();
            }

            $entity->checkPermission('@control');

            $em = $entity->evaluationMethodConfiguration;
            
            if($valuers = $entity->getEvaluationCommittee(true)) {
                foreach($valuers as $relation) {
                    $em->getUserRelation($relation->agent->user)->updateSummary(flush: true);
                    $app->log->debug("Atualiza resumo das avaliações do usuário {$relation->agent->user->id}");
                }
            }
         });


        $app->hook('panel.nav', function(&$nav_items) use($app) {
            $nav_items['opportunities']['items'] = [
                ['route' => 'panel/opportunities', 'icon' => 'opportunity', 'label' => i::__('Minhas oportunidades')],
                ['route' => 'panel/registrations', 'icon' => 'opportunity', 'label' => i::__('Minhas inscrições')],
                ['route' => 'panel/evaluations', 'icon' => 'opportunity', 'label' => i::__('Minhas avaliações'), 'condition' => function () use($app) {
                    return $app->user->getIsEvaluator();
                }]
            ];
        });

        $app->hook('Theme::addOpportunityBreadcramb', function($unused, $label) use($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            /** @var Opportunity $entity */
            $entity = $this->controller->requestedEntity;

            $is_valuer = false;

            if($entity instanceof EvaluationMethodConfiguration) {
                $first_phase = $entity->opportunity->firstPhase;
                $relation = $entity->getUserRelation($app->user);

                $is_valuer = $relation && $relation->status === AgentRelation::STATUS_ENABLED;
            } else {
                $first_phase = $entity->firstPhase;

                if ($entity->evaluationMethodConfiguration) {
                    $relation = $entity->evaluationMethodConfiguration->getUserRelation($app->user);
                    $is_valuer = $relation && $relation->status === AgentRelation::STATUS_ENABLED;
                }
            }

            if ($is_valuer) {
                $breadcrumb = [
                    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
                    ['label'=> i::__('Minhas avaliações')],
                    ['label'=> $first_phase->name, 'url' => $app->createUrl('opportunity', 'single', [$first_phase->id])]
                ];
            } else {
                $breadcrumb = [
                    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
                    ['label'=> i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
                    ['label'=> $first_phase->name, 'url' => $app->createUrl('opportunity', 'edit', [$first_phase->id])]
                ];
            } 
            
            if ($entity->isFirstPhase) {
                $breadcrumb[] = ['label'=> i::__('Período de inscrição')];
            } else {
                $breadcrumb[] = ['label'=> $entity->name];
            }
            $breadcrumb[] = ['label'=> $label];
            
            $this->breadcrumb = $breadcrumb;
        });

        $app->hook('Theme::useOpportunityAPI', function () use ($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            $this->enqueueScript('components', 'opportunities-api', 'js/OpportunitiesAPI.js', ['components-api']);
        });

        $app->hook('Theme::addAvaliableEvaluationFields', function () use ($app) {
            $entity = $this->controller->requestedEntity;
            $app->view->jsObject['avaliableEvaluationFields'] = $entity->opportunity->avaliableEvaluationFields;
        });

        $app->hook('Theme::addEvaluationInfosToJs', function () use ($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */

            $opportunity = $this->controller->requestedEntity;
            $configuration = $opportunity->evaluationMethodConfiguration;
            $infos = (array) $configuration->infos;

            $this->jsObject['evaluationInfos'] = $infos;
        });

        $app->hook('Theme::addRegistrationPhasesToJs', function ($unused = null, $registration = null) use ($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            $this->useOpportunityAPI();
            if (!$registration) {
                $registration = $this->controller->requestedEntity;
            }

            $number = $registration->number;

            $registrations = $app->repo('Registration')->findBy(['number' => $number]);

            $phases = [];

            foreach($registrations as $reg) {
                /** @var \MapasCulturais\Entities\Registration $reg */
                $phases[$reg->opportunity->id] = $reg->jsonSerialize();
                $phases[$reg->opportunity->id]['currentUserPermissions'] = $reg->getUserPermissions();
            }

            $this->jsObject['registrationPhases'] = $phases;
        });

        $app->hook('Theme::addOpportunityPhasesToJs', function ($unused = null, $opportunity = null) use ($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            $this->useOpportunityAPI();
            if (!$opportunity) {
                $entity = $this->controller->requestedEntity;

                if ($entity instanceof Opportunity) {
                    $opportunity = $entity;
                } else if ($entity instanceof Registration) {
                    $opportunity = $entity->opportunity;
                } else if ($entity instanceof EvaluationMethodConfiguration) {
                    $opportunity = $entity->opportunity;
                } else {
                    throw new Exception();
                }
            }
            $this->jsObject['opportunityPhases'] = $opportunity->firstPhase->phases;
        });

        $app->hook('Theme::addRegistrationFieldsToJs', function ($unused = null, $opportunity = null) use ($app) {
            if (!$opportunity) {
                $entity = $this->controller->requestedEntity;

                if ($entity instanceof Opportunity) {
                    $opportunity = $entity;
                } else if ($entity instanceof Registration) {
                    $opportunity = $entity->opportunity;
                } else {
                    throw new Exception();
                }
            }
            
            $fields = array_merge((array) $opportunity->registrationFileConfigurations, (array) $opportunity->registrationFieldConfigurations);

            usort($fields, function($a, $b) {                
                return $a->displayOrder <=> $b->displayOrder;
            });

            $this->jsObject['registrationFields'] = $fields;
        });

        $app->hook('mapas.printJsObject:before', function() use($app) {
            /** @var \MapasCulturais\Themes\BaseV2\Theme $this */
            $this->jsObject['config']['evaluationMethods'] = $app->getRegisteredEvaluationMethods();
        });

        // Na edição de campo enviar revisão
        $app->hook('entity(RegistrationFieldConfiguration).update:after', function() use($app) {
            /** @var \MapasCulturais\Entities\Opportunity $owner */
            $owner = $this->owner;
            $owner->_newModifiedRevision(sprintf(i::__('campo "%s" modificado'), $this->fieldName));
        });

        // Na criação de campo enviar revisão
        $app->hook('entity(RegistrationFieldConfiguration).insert:after', function() use($app) {
            /** @var \MapasCulturais\Entities\Opportunity $owner */
            $owner = $this->owner;
            $owner->_newModifiedRevision(sprintf(i::__('campo "%s" adicionado'), $this->fieldName));
        });

        // Na remoção de campo enviar revisão
        $app->hook('entity(RegistrationFieldConfiguration).remove:before', function() use($app) {
            /** @var \MapasCulturais\Entities\Opportunity $owner */
            $owner = $this->owner;
            $owner->_newModifiedRevision(sprintf(i::__('campo "%s" removido'), $this->fieldName));
        });

        // Na edição de anexo enviar revisão
        $app->hook('entity(RegistrationFileConfiguration).update:after', function() use($app) {
            /** @var \MapasCulturais\Entities\Opportunity $owner */
            $owner = $this->owner;
            $owner->_newModifiedRevision(sprintf(i::__('anexo "%s" modificado'), $this->fileGroupName));
        });

        // Na criação de anexo enviar revisão
        $app->hook('entity(RegistrationFileConfiguration).insert:after', function() use($app) {
            /** @var \MapasCulturais\Entities\Opportunity $owner */
            $owner = $this->owner;
            $owner->_newModifiedRevision(sprintf(i::__('anexo "%s" adicionado'), $this->fileGroupName));
        });

        // Na criação de anexo enviar revisão
        $app->hook('entity(RegistrationFileConfiguration).remove:before', function() use($app) {
            /** @var \MapasCulturais\Entities\Opportunity $owner */
            $owner = $this->owner;
            $owner->_newModifiedRevision(sprintf(i::__('anexo "%s" removido'), $this->fileGroupName));
        });

        // adiciona o parecer ao jsonSerialize da registration
        $app->hook('entity(Registration).jsonSerialize', function (&$data) use($app) {
            /** @var \MapasCulturais\Entities\Registration $this */
            $opportunity = $this->opportunity;
            if ($opportunity->publishedRegistrations && ($evaluation_configuration = $opportunity->evaluationMethodConfiguration)) {
                if ($evaluation_configuration->publishEvaluationDetails){
                    $em = $evaluation_configuration->evaluationMethod;
                    $data['consolidatedDetails'] = $em->getConsolidatedDetails($this);
                    $data['evaluationsDetails'] = [];

                    $evaluations = $this->sentEvaluations;

                    foreach($evaluations as $eval) {
                        $detail = $em->getEvaluationDetails($eval);
                        if ($evaluation_configuration->publishValuerNames){
                            $detail['valuer'] = $eval->user->profile->simplify('id,name,singleUrl');
                        }
                        $data['evaluationsDetails'][] = $detail;
                    }
                }
            }
        });

        $app->hook('entity(Registration).propertiesMetadata', function(&$result) {
            $result['consolidatedDetails'] = [
                'isMetadata' => false,
                'isEntityRelation' => false,
                'isReadonly' => true,
                'label' => i::__('Detalhes consolidados da avaliação')
            ];

            $result['evaluationsDetails'] = [
                'isMetadata' => false,
                'isEntityRelation' => false,
                'isReadonly' => true,
                'label' => i::__('Detalhes das avaliações')
            ];
        });

       // Atualiza a coluna metadata da relação do agente com a avaliação com od dados do summary das avaliações no momento de inserir, atualizar ou remover.
        $app->hook("entity(RegistrationEvaluation).<<insert|update|remove>>:after", function() use ($app) {
            /** @var RegistrationEvaluation $this */
            $opportunity = $this->registration->opportunity;

            $user = $app->user;
            if ($opportunity->canUser('@control')) {
                $user = $this->user;
            }

            if ($em = $this->getEvaluationMethodConfiguration()) {
                $em->enqueueUpdateSummary();
            }
        });

        // Atualiza a coluna metadata da relação do agente com a avaliação com od dados do summary das avaliações no momento da alteração de status.
        $app->hook("entity(RegistrationEvaluation).setStatus(<<*>>)", function() use ($app) {
            /** @var \MapasCulturais\Entities\RegistrationEvaluation $this */
            $opportunity = $this->registration->opportunity;

            $user = $app->user;
            if ($opportunity->canUser('@control')) {
                $user = $this->user;
            }

            if ($em = $this->getEvaluationMethodConfiguration()) {
                $em->getUserRelation($user)->updateSummary(flush: true);
            }
        });

        $app->hook("entity(Registration).recreatePermissionCache:after", function(&$users) use ($app) {
            /** 
             * @var \MapasCulturais\Entities\Registration $this 
             * @var \MapasCulturais\Entities\EvaluationMethodConfiguration $em
             */

            if($em = $this->getEvaluationMethodConfiguration()) {
                $em->enqueueUpdateSummary();
            }
        });

        // Atualiza a coluna metadata da relação do agente com a avaliação com od dados do summary das avaliações no momento que se atribui uma avaliação.
        $app->hook("entity(EvaluationMethodConfiguration).recreatePermissionCache:after", function(&$users) use ($app) {
            /** @var \MapasCulturais\Entities\EvaluationMethodConfiguration $this */
            $this->enqueueUpdateSummary();
        });

    }

    function register(){
        $app = App::i();
        $controllers = $app->getRegisteredControllers();
        if (!isset($controllers['opportunities'])) {
            $app->registerController('opportunities', Controller::class);
        }

        // after plugin registration that creates the configuration types
        $app->hook('app.register', function(){
            $this->view->registerMetadata(EvaluationMethodConfiguration::class, 'infos', [
                'label' => i::__("Textos informativos para as fichas de avaliação"),
                'type' => 'json',
            ]);
        });

        $this->registerEvauationMethodConfigurationMetadata('publishEvaluationDetails', [
            'label' => i::__('Publicar os pareceres para o proponente'),
            'type' => 'json',
        ]);

        $this->registerEvauationMethodConfigurationMetadata('publishValuerNames', [
            'label' => i::__('Publicar o nome dos avaliadores nos pareceres'),
            'type' => 'json',
        ]);
           
    }
}