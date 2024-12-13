<?php

namespace Opportunities;

use DateTime;
use Exception;
use MapasCulturais\App;
use MapasCulturais\Entities\AgentRelation;
use MapasCulturais\i;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Entities\RegistrationStep;
use MapasCulturais\Entities\Agent;
use MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation;
use MapasCulturais\Entities\EvaluationMethodConfigurationMeta;

class Module extends \MapasCulturais\Module{

    function __construct(array $config = [])
    {
        $app = App::i();
        parent::__construct($config);
    }

    function _init(){

        /** @var App $app */
        $app = App::i();

        $self = $this;

        // Registro de Jobs
        $app->registerJobType(new Jobs\StartEvaluationPhase(Jobs\StartEvaluationPhase::SLUG));
        $app->registerJobType(new Jobs\StartDataCollectionPhase(Jobs\StartDataCollectionPhase::SLUG));
        $app->registerJobType(new Jobs\FinishEvaluationPhase(Jobs\FinishEvaluationPhase::SLUG));
        $app->registerJobType(new Jobs\FinishDataCollectionPhase(Jobs\FinishDataCollectionPhase::SLUG));
        $app->registerJobType(new Jobs\PublishResult(Jobs\PublishResult::SLUG));
        $app->registerJobType(new Jobs\UpdateSummaryCaches(Jobs\UpdateSummaryCaches::SLUG));
        $app->registerJobType(new Jobs\RedistributeCommitteeRegistrations(Jobs\RedistributeCommitteeRegistrations::SLUG));

        $app->hook('mapas.printJsObject:before', function () {
            /** @var \MapasCulturais\Theme $this */
            $this->jsObject['EntitiesDescription']['registrationstep'] = \MapasCulturais\Entities\RegistrationStep::getPropertiesMetadata();
        });

        $app->hook('entity(Opportunity).insert:after', function() {
            $step = new RegistrationStep();
            $step->name = '';
            $step->opportunity = $this;
            $step->save(true);
        });

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

        /** 
         * Enfileiramento dos JOBs de distribuição de avaliadores
         */
        $app->hook('entity(EvaluationMethodConfigurationAgentRelation).<<insert|update|delete>>:finish', function() use($app) {
            /** @var EvaluationMethodConfigurationAgentRelation $this */
            $app->enqueueJob(Jobs\RedistributeCommitteeRegistrations::SLUG, ['evaluationMethodConfiguration' => $this->owner]);
        });
        $_metadata_list = 'valuersPerRegistration|ignoreStartedEvaluations|fetchFields|fetchSelectionFields|fetch|fetchCategories|fetchRanges|fetchProponentTypes';
        $app->hook("entity(EvaluationMethodConfiguration).meta(<<{$_metadata_list}>>).<<insert|update|delete>>:after", function() use($app) {
            /** @var EvaluationMethodConfigurationMeta $this */
            $this->owner->mustRedistributeCommitteeRegistrations = true;
        });

        $app->hook('entity(EvaluationMethodConfiguration).save:finish', function () use($app) {
            /** @var EvaluationMethodConfiguration $this */
            if ($this->mustRedistributeCommitteeRegistrations) {
                $app->enqueueJob(Jobs\RedistributeCommitteeRegistrations::SLUG, ['evaluationMethodConfiguration' => $this]);    
            }
        });

        $app->hook('entity(RegistrationEvaluation).send:after', function() use($app) {
            /** @var Registration $this */
            $app->enqueueJob(Jobs\RedistributeCommitteeRegistrations::SLUG, ['evaluationMethodConfiguration' => $this->evaluationMethodConfiguration]);
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

        $app->hook('entity(EvaluationMethodConfiguration).propertiesMetadata', function(&$result) {
            $result['useCommitteeGroups'] = [
                'isMetadata' => false,
                'isEntityRelation' => false,
                'isReadonly' => true,
                'label' => i::__('Indica se pode utilizar grupos de comissão de avaliação')
            ];
            $result['evaluateSelfApplication'] = [
                'isMetadata' => false,
                'isEntityRelation' => false,
                'isReadonly' => true,
                'label' => i::__('Indica se pode ser utilizada a auto aplicação de resultados')
            ];
        });

       // Atualiza a coluna metadata da relação do agente com a avaliação com od dados do summary das avaliações no momento de inserir, atualizar ou remover.
        $app->hook("entity(RegistrationEvaluation).<<insert|update|remove>>:after", function() use ($app) {
            $opportunity = $this->registration->opportunity;

            $user = $app->user;
            if ($opportunity->canUser('@control')) {
                $user = $this->user;
            }

            if ($em = $this->getEvaluationMethodConfiguration()) {
                $em->getUserRelation($user)->updateSummary(flush: true);
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
            /** @var \MapasCulturais\Entities\Registration $this */
            if($em = $this->getEvaluationMethodConfiguration()) {
                $relations = $em->getAgentRelations();
                foreach($relations as $relation) {
                    $relation->updateSummary(flush: true, started: false, completed: false, sent: false);
                }
            }
        });

        // Atualiza a coluna metadata da relação do agente com a avaliação com od dados do summary das avaliações no momento que se atribui uma avaliação.
        $app->hook("entity(EvaluationMethodConfiguration).recreatePermissionCache:after", function(&$users) use ($app) {
            /** @var \MapasCulturais\Entities\EvaluationMethodConfiguration $this */
            if($users) {
                foreach ($users as $user) {
                    $relation = $app->repo('EvaluationMethodConfigurationAgentRelation')->findOneBy(['agent' => $user->profile, 'owner' => $this]);
                    if ($relation) {
                        /** @var \MapasCulturais\Entities\EvaluationMethodConfigurationAgentRelation */
                        $relation->updateSummary(flush: true, started: false, completed: false, sent: false);
                    }
                }
            }
        });

        $app->hook("entity(EvaluationMethodConfiguration).renameAgentRelationGroup:before", function($old_name, $new_name, $relations) {
            /** @var \MapasCulturais\Entities\EvaluationMethodConfiguration $this */

            if(isset($this->valuersPerRegistration->{$old_name})) {
                $evaluator_count = $this->valuersPerRegistration;
                $evaluator_count->{$new_name} = $evaluator_count->{$old_name};
                unset($evaluator_count->{$old_name});

                $this->valuersPerRegistration = $evaluator_count;
            }

            if(isset($this->fetchFields->{$old_name})) {
                $registration_filter_config = $this->fetchFields;
                $registration_filter_config->{$new_name} = $registration_filter_config->{$old_name};
                unset($registration_filter_config->{$old_name});

                $this->fetchFields = $registration_filter_config;
            }
        });

        $app->hook("entity(RegistrationEvaluation).send:after", function() use ($app) {
            /** @var \MapasCulturais\Entities\RegistrationEvaluation $this */
            $registration = $this->registration;
            $opportunity = $registration->opportunity;
            $evaluation_type = $opportunity->evaluationMethodConfiguration->type->id;

            if($opportunity->evaluationMethodConfiguration->autoApplicationAllowed) {
                if($registration->needsTiebreaker() && !$registration->evaluationMethod->getTiebreakerEvaluation($registration)) {
                    return;
                }
                $conn = $app->em->getConnection();
                $evaluations = $conn->fetchAll("
                    SELECT
                       *
                    FROM
                        evaluations
                    WHERE
                        registration_id = {$registration->id}"
                );

                $all_status_sent = true;

                foreach ($evaluations as $evaluation) {
                    if ($evaluation['evaluation_status'] !== RegistrationEvaluation::STATUS_SENT) {
                        $all_status_sent = false;
                    }
                }

                if ($all_status_sent) {
                    if($evaluation_type == 'simple') {
                        $value = $registration->consolidatedResult;
                    }

                    if($evaluation_type == 'documentary') {
                        $value = $registration->consolidatedResult == 1 ? Registration::STATUS_APPROVED : Registration::STATUS_NOTAPPROVED;
                    }

                    if($evaluation_type == 'qualification') {
                        $value = $registration->consolidatedResult == 'Habilitado' ? Registration::STATUS_APPROVED : Registration::STATUS_NOTAPPROVED;
                    }

                    $app->disableAccessControl();
                    $registration->setStatus($value);
                    $registration->save();
                    $app->enableAccessControl();
                }
            }
        });

        // Adiciona os selos de acordo com os proponent
        $app->hook("entity(Registration).status(approved)", function() use($app, $self){
            /** @var \MapasCulturais\Entities\Registration $this */

            $opportunity = $this->opportunity;

            if ($opportunity && ($opportunity->publishedRegistrations || $this->opportunity->firstPhase->isContinuousFlow)) {
               $seals = $opportunity->proponentSeals;
               $proponent_type = $this->proponentType;
               $owner = $this->owner;
               $proponent_typesTo_agents_Map = $app->config['registration.proponentTypesToAgentsMap'];
               $categories_seals = $opportunity->categorySeals;
               $category = $this->category;

               if($proponent_type){

                   if (array_key_exists($proponent_type, $proponent_typesTo_agents_Map)) {
                       $agent_type = $proponent_typesTo_agents_Map[$proponent_type];
                       if (isset($seals->$proponent_type)) {
                            $proponent_seals = $seals->{$proponent_type};
                            if($agent_type == "owner"){
                               $self->applySeal($owner,$proponent_seals);
                            }

                            if($agent_type == "coletivo"){
                                $agents = $this->getAgentRelations();
                                $self->applySeal($agents[0]->agent, $proponent_seals);
                            }
                        }
                    }

                    // Se a inscrição tiver "tipo de proponente" e "categoria", adicionar o selo verificador da categoria, caso possua.
                    if($category) {
                        if (array_key_exists($proponent_type, $proponent_typesTo_agents_Map)) {
                            $agent_type = $proponent_typesTo_agents_Map[$proponent_type];

                            if (isset($categories_seals->{$category})) {
                                $category_seals = $categories_seals->{$category};

                                // Verifica se a opção "Habilitar a vinculação de agente coletivo" esta ativa
                                if(isset($opportunity->firstPhase->useAgentRelationColetivo) && $opportunity->firstPhase->useAgentRelationColetivo == 'required') {
                                    if($agent_type == "coletivo"){
                                        $agents = $this->getAgentRelations();
                                        $self->applySeal($agents[0]->agent, $category_seals);
                                    }
                                }

                                if($agent_type == "owner"){
                                   $self->applySeal($owner, $category_seals);
                                }
                            }

                        }
                    }
                }

                // Se tiver apenas "categoria" e não houver "tipo de proponente", adicionar selo verificador (caso configurado) apenas no agente individual
                if($category && !$proponent_type) {
                    if (isset($categories_seals->{$category})) {
                        $category_seals = $categories_seals->{$category};
                        $self->applySeal($owner, $category_seals);
                    }
                }
            }
        });

        $app->hook("entity(Registration).status(<<draft|waitlist|notapproved|invalid|sent|>>)", function() use($app, $self){
            /** @var \MapasCulturais\Entities\Registration $this */

            $opportunity = $this->opportunity;

            if ($opportunity && ($opportunity->publishedRegistrations || $this->opportunity->firstPhase->isContinuousFlow)) {
                $seals = $opportunity->proponentSeals;
                $proponent_type = $this->proponentType;
                $owner = $this->owner;
                $categories_seals = $opportunity->categorySeals;
                $category = $this->category;

                if ($proponent_type) {
                    $proponent_seals = $seals->{$proponent_type};
                    $proponent_typesTo_agents_Map = $app->config['registration.proponentTypesToAgentsMap'];
                    $agent_type = $proponent_typesTo_agents_Map[$proponent_type] ?? null;


                    if ($agent_type == "owner") {
                        $relations = $owner->getSealRelations();
                        $self->removeSeals($app, $relations, $proponent_seals);
                    }

                    if ($agent_type == "coletivo") {
                        $agent_relations = $this->getAgentRelations();

                        foreach ($agent_relations as $agent_relation) {
                            $agent = $agent_relation->agent;
                            $relations = $agent->getSealRelations();
                            $self->removeSeals($app, $relations, $proponent_seals);
                        }
                    }

                    // Se a inscrição tiver "tipo de proponente" e "categoria", remover o selo verificador da categoria, caso possua.
                    if($category) {
                        if (isset($categories_seals->{$category})) {
                            $category_seals = $categories_seals->{$category};

                             // Verifica se a opção "Habilitar a vinculação de agente coletivo" esta ativa
                            if(isset($opportunity->firstPhase->useAgentRelationColetivo) && $opportunity->firstPhase->useAgentRelationColetivo == 'required') {
                                if ($agent_type == "coletivo") {
                                    $agent_relations = $this->getAgentRelations();
    
                                    foreach ($agent_relations as $agent_relation) {
                                        $agent = $agent_relation->agent;
                                        $relations = $agent->getSealRelations();
                                        $self->removeSeals($app, $relations, $category_seals);
                                    }
                                }
                            }

                            if ($agent_type == "owner") {
                                $relations = $owner->getSealRelations();
                                $self->removeSeals($app, $relations, $category_seals);
                            }
                        }
                    }
                }

                // Se tiver apenas "categoria" e não houver "tipo de proponente", remover selo verificador (caso configurado) do agente individual
                if($category && !$proponent_type) {
                    if (isset($categories_seals->{$category})) {
                        $category_seals = $categories_seals->{$category};
                        $relations = $owner->getSealRelations();
                        $self->removeSeals($app, $relations, $category_seals);
                    }
                }
            }
        });

        //Adicionar selos conforme a publicação de resultado
        $app->hook("entity(Opportunity).publishRegistrations:after", function() use($app, $self){
            /** @var \MapasCulturais\Entities\Opportunity $this */

            if($this->proponentSeals || $this->categorySeals){
                $proponent_typesTo_agents_Map = $app->config['registration.proponentTypesToAgentsMap'];
                $registrations = $app->repo('Registration')->findBy(['opportunity' => $this]);

                foreach($registrations as $registration){

                    if($registration->status == Registration::STATUS_APPROVED){
                    $proponent_type = $registration->proponentType;
                        $category = $registration->category;
                        $categories_seals = $this->categorySeals;
                        $owner = $registration->owner;

                        if($proponent_type){
                            $proponent_seals = $this->proponentSeals->{$proponent_type};
                            $agent_type = $proponent_typesTo_agents_Map[$proponent_type];

                            if($agent_type == "owner"){
                                $self->applySeal($owner,$proponent_seals);
                            }

                            if($agent_type == "coletivo"){
                                if($agents = $registration->getAgentRelations()){
                                    $self->applySeal($agents[0]->agent, $proponent_seals);
                                }
                            }

                            if($category) {
                                if (array_key_exists($proponent_type, $proponent_typesTo_agents_Map)) {
                                    if (isset($categories_seals->{$category})) {
                                        $category_seals = $categories_seals->{$category};
        
                                        // Verifica se a opção "Habilitar a vinculação de agente coletivo" esta ativa
                                        if(isset($this->firstPhase->useAgentRelationColetivo) && $this->firstPhase->useAgentRelationColetivo == 'required') {
                                            if($agent_type == "coletivo"){
                                                $agents = $registration->getAgentRelations();
                                                $self->applySeal($agents[0]->agent, $category_seals);
                                            }
                                        }
        
                                        if($agent_type == "owner"){
                                           $self->applySeal($owner, $category_seals);
                                        }
                                    }
                                } 
                            }
                        }

                        if($category && !$proponent_type) {
                            if (isset($categories_seals->{$category})) {
                                $category_seals = $categories_seals->{$category};
                                $self->applySeal($owner, $category_seals);
                            }
                        }
                    }
                }
            }
        });

        $app->hook("entity(Opportunity).unpublishRegistrations:after", function() use($app, $self) {
            /** @var \MapasCulturais\Entities\Opportunity $this */
            if ($this->proponentSeals || $this->categorySeals) {

                $proponent_typesTo_agents_Map = $app->config['registration.proponentTypesToAgentsMap'];
                $registrations = $app->repo('Registration')->findBy(['opportunity' => $this]);

                foreach ($registrations as $registration) {
                    if ($registration->status == \MapasCulturais\Entities\Registration::STATUS_APPROVED) {
                        $proponent_type = $registration->proponentType;
                        $categories_seals = $this->categorySeals;
                        $category = $registration->category;
                        $owner = $registration->owner;

                        if ($proponent_type) {
                            $proponent_seals = $this->proponentSeals->{$proponent_type};
                            $agent_type = $proponent_typesTo_agents_Map[$proponent_type];

                            if ($agent_type == "owner") {
                                $relations = $owner->getSealRelations();
                                $self->removeSeals($app, $relations, $proponent_seals);
                            }

                            if ($agent_type == "coletivo") {
                                $agent_relations = $registration->getAgentRelations();

                                foreach ($agent_relations as $agent_relation) {
                                    $agent = $agent_relation->agent;
                                    $relations = $agent->getSealRelations();
                                    $self->removeSeals($app, $relations, $proponent_seals);
                                }
                            }

                            if($category) {
                                if (isset($categories_seals->{$category})) {
                                    $category_seals = $categories_seals->{$category};
        
                                    // Verifica se a opção "Habilitar a vinculação de agente coletivo" esta ativa
                                    if(isset($this->firstPhase->useAgentRelationColetivo) && $this->firstPhase->useAgentRelationColetivo == 'required') {
                                        if ($agent_type == "coletivo") {
                                            $agent_relations = $this->getAgentRelations();
            
                                            foreach ($agent_relations as $agent_relation) {
                                                $agent = $agent_relation->agent;
                                                $relations = $agent->getSealRelations();
                                                $self->removeSeals($app, $relations, $category_seals);
                                            }
                                        }
                                    }
        
                                    if ($agent_type == "owner") {
                                        $relations = $owner->getSealRelations();
                                        $self->removeSeals($app, $relations, $category_seals);
                                    }
                                }
                            }
                        }

                        // Se tiver apenas "categoria" e não houver "tipo de proponente", remover selo verificador (caso configurado) do agente individual
                        if($category && !$proponent_type) {
                            if (isset($categories_seals->{$category})) {
                                $category_seals = $categories_seals->{$category};
                                $relations = $owner->getSealRelations();
                                $self->removeSeals($app, $relations, $category_seals);
                            }
                        }
                    }
                }
            }
        });
    }

    function register(){
        $app = App::i();

        $app->registerController('registrationstep', \MapasCulturais\Controllers\RegistrationStep::class);

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

        $this->registerEvauationMethodConfigurationMetadata('autoApplicationAllowed', [
            'label' => i::__('Autoaplicação de resultados'),
            'type' => 'boolean',
            'default' => false
        ]);
        $this->registerOpportunityMetadata('proponentSeals', [
            'label' => i::__('Selos de certificação'),
            'type' => 'json',
        ]);

        $this->registerOpportunityMetadata('categorySeals', [
            'label' => i::__('Selos de certificação para as categorias'),
            'type' => 'json',
        ]);

        $this->registerOpportunityMetadata('isContinuousFlow', [
            'label' => i::__('Edital de fluxo contínuo'),
            'type' => 'boolean',
            'default' => false,
        ]);

        $this->registerOpportunityMetadata('hasEndDate', [
            'label' => i::__('Definir data final para inscrições'),
            'type' => 'boolean',
            'default' => false,
        ]);

        $this->registerOpportunityMetadata('proponentAgentRelation', [
            'label' => i::__('Vinculação de Agente coletivo para tipos de proponente'),
            'type' => 'object',
            'description' => i::__('Armazena se a vinculação de agente coletivo está habilitada para Coletivo ou Pessoa Jurídica'),
        ]);

        $this->registerEvauationMethodConfigurationMetadata('fetchFields', [
            'label' => i::__('Configuração filtro de inscrição para avaliadores/comissão'),
            'type' => 'object',
        ]);
        
        $this->registerEvauationMethodConfigurationMetadata('valuersPerRegistration', [
            'label' => i::__('Quantidade de avaliadores por inscrição'),
            'type' => 'object',
        ]);

        $this->registerEvauationMethodConfigurationMetadata('ignoreStartedEvaluations', [
            'label' => i::__('Quantidade de avaliadores por inscrição'),
            'type' => 'object',
        ]);

        $this->registerEvauationMethodConfigurationMetadata('fetchSelectionFields', [
            'label' => i::__('Configuração de campos de seleção'),
            'type' => 'object',
        ]);

        $this->registerEvauationMethodConfigurationMetadata('fetch', [
            'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores'),
            'type' => 'json'
        ]);

        $this->registerEvauationMethodConfigurationMetadata('fetchCategories', [
            'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores por categoria'),
            'type' => 'object',
        ]);

        $this->registerEvauationMethodConfigurationMetadata('fetchRanges', [
            'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores por faixa'),
            'type' => 'object',
        ]);

        $this->registerEvauationMethodConfigurationMetadata('fetchProponentTypes', [
            'label' => i::__('Configuração da distribuição das inscrições entre os avaliadores por tipo de proponente'),
            'type' => 'object',
        ]);

        $this->registerEvauationMethodConfigurationMetadata('showExternalReviews', [
            'label' => i::__('Permite visualização de pareceres externos'),
            'type' => 'boolean',
            'default' => false,
        ]);
    }

    public function applySeal(Agent $agent, array $sealIds){
        $app = App::i();
        foreach($sealIds as $sealId) {
            $seal = $app->repo('Seal')->find($sealId);
            $relations = $agent->getSealRelations();

            $has_new_seal = false;
            foreach($relations as $relation){
                if($relation->seal->id == $seal->id){
                    $has_new_seal = true;
                    break;
                }
            }
            if(!$has_new_seal){
                $agent->createSealRelation($seal);
            }
        }
    }

    function removeSeals($app, $relations, $proponent_seals) {
        foreach ($proponent_seals as $proponent_seal) {
            $seal = $app->repo('Seal')->find($proponent_seal);

            foreach ($relations as $relation) {
                if ($relation->seal->id == $seal->id) {
                    $relation->owner->removeSealRelation($seal);
                    break;
                }
            }
        }
    }
}