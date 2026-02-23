<?php
namespace OpportunityPhases;

use MapasCulturais\API;
use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Connection;
use MapasCulturais\Definitions;
use MapasCulturais\Entities;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Exceptions;
use MapasCulturais\i;
use Opportunities\Jobs\UpdateSummaryCaches;

class Module extends \MapasCulturais\Module{

    /**
     * Retorna o oportunidade principal
     *
     * @return Opportunity
     */
    static function getBaseOpportunity(?Opportunity $opportunity = null){
        if(is_null($opportunity)){
            $opportunity = self::getRequestedOpportunity();
        }

        if(!$opportunity){
            return null;
        }

        if($opportunity->isOpportunityPhase){
            $opportunity = $opportunity->parent;
        }

        return $opportunity;
    }

    /**
     * Retorna o oportunidade/fase que está sendo visualizado
     *
     * @return Opportunity
     */
    static function getRequestedOpportunity(){
        $app = App::i();

        $opportunity = $app->view->controller->requestedEntity;

        if(!$opportunity){
            return null;
        }

        return $opportunity;
    }

    /**
     * Retorna a última fase do oportunidade
     *
     * @param Opportunity $opportunity
     * @return Opportunity
     */
    static function getLastCreatedPhase(Opportunity $opportunity) {
        $app = App::i();

        $base_opportunity = self::getBaseOpportunity($opportunity);

        $params = [
            '@select'=>'id',
            'parent' => "EQ({$base_opportunity->id})",
            'status' => 'GTE(-1)',
            '@permissions' => 'view',
            '@order' => 'registrationFrom DESC',
            '@limit' => 1
        ];

        $app->applyHook('entity(Opportunity).getLastCreatedPhase:params', [$base_opportunity, &$params]);

        $query = new ApiQuery(Opportunity::class, $params);

        if ($ids = $query->findIds()) {
            $last_phase = $app->repo('Opportunity')->find($ids[0]);
        } else {
            $last_phase = $base_opportunity;
        }

        return $last_phase;
    }

    /**
     * Retorna a última fase que teve seu período de inscrição terminado
     * @param Opportunity $base_opportunity
     * @return Opportunity
     */
    static function getLastCompletedPhase(Opportunity $base_opportunity){
        $now = new \DateTime;

        if($base_opportunity->registrationTo > $now){
            return null;
        }

        $result = $base_opportunity;
        $phases = self::getPhases($base_opportunity);

        foreach($phases as $phase){
            if($phase->registrationTo <= $now){
                $result = $phase;
            }
        }

        return $result;
    }

    /**
     * Retorna a fase atual
     * @param Opportunity $base_opportunity
     * @return Opportunity
     */
    static function getCurrentPhase(Opportunity $base_opportunity){
        $now = new \DateTime;

        $result = $base_opportunity;
        $phases = self::getPhases($base_opportunity);

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
     * @param Opportunity $phase
     * @return Opportunity a fase anterior
     */
    static function getPreviousPhase(Opportunity $phase){
        if (!$phase->isOpportunityPhase) {
            return null;
        }

        $base_opportunity = self::getBaseOpportunity($phase);

        $phases = self::getPhases($base_opportunity);

        $result = $base_opportunity;

        foreach($phases as $p){
            if ($p->registrationFrom && $phase->registrationFrom) {
                if ($p->registrationFrom < $phase->registrationFrom) {
                    $result = $p;
                }
            } else if ($p->createTimestamp != $phase->createTimestamp) {
                if ($p->createTimestamp < $phase->createTimestamp) {
                    $result = $p;
                }
            } else {
                if ($p->id < $phase->id) {
                    $result = $p;
                }
            }
        }

        return $result;
    }


    /**
     * Retorna as fases do oportunidade informado
     *
     * @param Opportunity $opportunity
     * @return Opportunity[]
     */
    static function getPhases(Opportunity $opportunity){
        if ($opportunity->canUser('@control')) {
            $status = [0,-1];
        } else {
            $status = -1;
        }

        $app = App::i();
        $phases = $app->repo('Opportunity')->findBy([
            'parent' => $opportunity,
            'status' => $status
        ],['registrationTo' => 'ASC', 'id' => 'ASC']);

        $phases = array_filter($phases, function($item) {
            if($item->isOpportunityPhase){
                return $item;
            }
        });

        return $phases;
    }

    static function getPreviousPhaseRegistration($registration){
        $app = App::i();
        $previous = null;

        if($prev_id = $registration->previousPhaseRegistrationId){
            $previous = $app->repo('Registration')->find($prev_id);
        }

        return $previous;
    }

    function _init () {
        $app = App::i();
        $self = $this;
        $registration_repository = $app->repo('Registration');

        $app->hook("entity(Registration).<<insert|send>>:before", function(){
            if(!$this->opportunity->isDataCollection){
              $this->sentTimestamp = $this->previousPhase->sentTimestamp;
            }
        });

        $app->hook("entity(Registration).status(<<*>>)", function(){
            if($this->previousPhase && !$this->opportunity->isDataCollection && $this->status > 0){
                $this->sentTimestamp = $this->previousPhase->sentTimestamp;
            }
        });

        // Redireciona o usuario sempre para a primeira fase
        $app->hook("GET(opportunity.<<single|edit>>):<<*>>", function() use ($app) {
            $entity = $this->requestedEntity;
            if(!$entity->isFirstPhase){
                $url = $app->createUrl("opportunity",$this->action,[$entity->firstPhase->id]);
                $app->redirect($url);
            }
        });

        $app->hook('view.partial(singles/registration-edit--categories).params', function(&$params, &$template) use ($app) {
            if($this->controller->requestedEntity->opportunity->isOpportunityPhase && !$this->controller->requestedEntity->preview) {
                $template = '_empty';
                return;
            }
        });

        $app->hook('view.partial(singles/registration-edit--agents).params', function(&$params, &$template) use ($app) {
            if($this->controller->requestedEntity->opportunity->isOpportunityPhase) {
                $template = '_empty';
                return;
            }
        });

        $app->view->enqueueStyle('app', 'plugin-opportunity-phases', 'css/opportunity-phases.css');

        /**
         * Getters das oportuniddes
         */

        $app->hook('entity(Opportunity).get(isFirstPhase)', function(&$value) {
            /** @var Opportunity $this */
           $value = !$this->parent;
        });

        $app->hook('entity(Opportunity).get(firstPhase)', function(&$value) {
            /** @var Opportunity $this */
            $value = $this->parent ? $this->parent : $this;
        });

        $app->hook('entity(Opportunity).get(previousPhase)', function(&$value) use ($app) {
            /** @var Opportunity $this */
            $first_phase = $this->firstPhase;
            if(!$first_phase->id) {
                return;
            }
            if($this->isNew()) {
                if($this->isReportingPhase) {
                    $value = $first_phase->lastPhase;
                    while($next_phase = $value->nextPhase) {
                        $value = $next_phase;
                    }
                } else {
                    $value = $first_phase->lastPhase->previousPhase;
                }
                return;
            }
            if($this->isFirstPhase) {
                return null;
            }

            if($this->isAppealPhase) {
                $value = $this->parent;
                return;
            }

            $this->enableCacheGetterResult('previousPhase');

            $last_phase = $this->isLastPhase ? $this : $this->lastPhase;

            $complement = "";
            if(!$this->isLastPhase) {
                $complement = "o.id < :this AND";
            }

            if(!$this->isReportingPhase) {
                $complement = "o.id <> :last AND o.id < :this AND";
            }

            $query = $app->em->createQuery("
                SELECT o
                FROM MapasCulturais\Entities\Opportunity o
                LEFT JOIN o.__metadata om WITH om.key = 'isAppealPhase'
                WHERE
                    {$complement}
                    (
                        o.id = :parent OR
                        (o.parent = :parent AND o.id <> :this)
                    )
                    AND om.value IS NULL
                ORDER BY o.id DESC");

            $query->setMaxResults(1);
            
            $params = [
                "parent" => $first_phase,
                "this" => $this,
            ];

            if(!$this->isReportingPhase) {
                $params["last"] = $last_phase;
            }

            $query->setParameters($params);

            $value = $query->getOneOrNullResult();
        });

        $app->hook('entity(Opportunity).get(previousPhases)', function(&$value) use ($app) {
            /** @var Opportunity $this */
            $first_phase = $this->firstPhase;
            if(!$first_phase->id) {
                return;
            }
            if($this->isNew()) {
                $value = $first_phase->lastPhase->previousPhases;
                return;
            }

            $this->enableCacheGetterResult('previousPhases');

            $class = Opportunity::class;
            $query = $app->em->createQuery("
                SELECT o
                FROM $class o
                WHERE
                    o.id = :parent OR
                    (o.parent = :parent AND o.registrationFrom < (SELECT this.registrationFrom FROM $class this WHERE this.id = :this))
                ORDER BY o.registrationFrom ASC");

            $query->setParameters([
                "parent" => $first_phase,
                "this" => $this
            ]);

            $value = $query->getResult();
        });

        $app->hook('entity(Opportunity).get(nextPhase)', function(&$value) use ($app) {
            /** @var Opportunity $this */
            $first_phase = $this->firstPhase;
            if(!$first_phase->id) {
                return;
            }
            if($this->isNew()) {
                if($this->isReportingPhase) {
                    $value = null;
                } else {
                    $value = $first_phase->lastPhase;
                }

                return;
            }

            $this->enableCacheGetterResult('nextPhase');

            $class = Opportunity::class;
            $last_phase = $this->lastPhase;

            $query = $app->em->createQuery("
                SELECT o
                FROM $class o
                WHERE
                    o.parent = :parent AND
                    o.id > :this AND
                    o.id <> :lastPhase
                ORDER BY o.id ASC");

            $query->setMaxResults(1);
            $query->setParameters([
                "parent" => $first_phase,
                "this" => $this,
                "lastPhase" => $last_phase
            ]);

            if($result = $query->getOneOrNullResult()) {
                $value = $result;
            } else if(!$this->isReportingPhase) {
                $value = $last_phase;
            }

        });

        $app->hook('entity(Opportunity).get(nextPhases)', function(&$value) use ($app) {
            /** @var Opportunity $this */
            $first_phase = $this->firstPhase;
            if(!$first_phase->id) {
                return;
            }
            if($this->isNew()) {
                $value = [$first_phase->lastPhase];
                return;
            }

            if($this->isLasPhase) {
                $value = [];
                return;
            }

            $this->enableCacheGetterResult('nextPhases');

            $class = Opportunity::class;
            $last_phase = $this->lastPhase;

            $query = $app->em->createQuery("
                SELECT o
                FROM $class o
                WHERE
                    o.parent = :parent AND
                    o.id > :this AND
                    o.id <> :lastPhase
                ORDER BY o.id ASC");

            $query->setParameters([
                "parent" => $first_phase,
                "this" => $this,
                "lastPhase" => $last_phase
            ]);

            $value = $query->getResult();
            $value[] = $last_phase;
        });

        /**
         * Retornar a lista de fases de coleta de dados, independentemente de se a coleta de dados está ou não habilitada.
         */
        $app->hook('entity(Opportunity).get(allPhases)', function(&$values) use ($app) {
            /** @var Opportunity $this */

            $first_phase = $this->firstPhase;
            if(!$first_phase->id) {
                return;
            }

            $this->enableCacheGetterResult('allPhases');

            $values = [$first_phase];
            $class = Opportunity::class;
            $query = $app->em->createQuery("
                SELECT o
                FROM $class o
                WHERE o.parent = :parent AND o.status = -1
                ORDER BY o.registrationFrom ASC, o.id ASC");

            $query->setParameters([
                "parent" => $first_phase,
            ]);

            $last_phase = null;
            $reporting_phases = [];

            foreach($query->getResult() as $opp) {
                if ($opp->isReportingPhase) {
                    $reporting_phases[] = $opp;
                } elseif ($opp->isLastPhase) {
                    $last_phase = $opp;
                } else {
                    $values[] = $opp;
                }
            }

            if($last_phase) {
                $values[] = $last_phase;
            }

            /**
             * Adiciona as fases de prestação de informações ao final da lista de fases.
             */
            if ($reporting_phases) {
                $values = array_merge($values, $reporting_phases);
            }
        });

        /**
         * retorna a lista de fases de coleta de dados e avaliação
         */
        $app->hook('entity(Opportunity).get(phases)', function (&$value) use($app) {
            /** @var Opportunity $this */

            $this->enableCacheGetterResult('phases');

            $result = [];
            $app->disableAccessControl();

            $firstPhase = $this->firstPhase;


            if($opportunity_phases = $firstPhase->allPhases){
                foreach($opportunity_phases as $key => $opportunity){
                    $mout_simplify = "id,name,summary,currentUserPermissions,relatedAgents,agentRelations";

                    $emc = $opportunity->evaluationMethodConfiguration;
                    if($opportunity->isDataCollection || $opportunity->isFirstPhase || $opportunity->isLastPhase){
                        $app->applyHook('module(OpportunityPhases).dataCollectionPhaseData', [&$mout_simplify]);

                        $item = $opportunity->simplify("{$mout_simplify},type,publishedRegistrations,publishTimestamp,registrationFrom,registrationTo,isFirstPhase,isLastPhase,isReportingPhase,isLastReportingPhase,files,statusLabels");
                        $item->appealPhase = $opportunity->appealPhase;

                        $item->registrationSteps = [];
                        foreach ($opportunity->registrationSteps as $step) {
                            $simplifiedStep = $step->simplify("id,name,displayOrder,metadata");
                            $item->registrationSteps[] = $simplifiedStep;
                        }

                        if($emc){
                            $item->evaluationMethodConfiguration = $emc->simplify("id,name,evaluationFrom,evaluationTo,useCommitteeGroups,evaluateSelfApplication");
                            $item->evaluationMethodConfiguration->appealPhase = $emc->appealPhase;
                        }

                        $result[] = $item;
                    }

                    if($emc){

                        if($opportunity->isDataCollection){
                            $mout_simplify.=",ownerId";
                        }

                        $app->applyHook('module(OpportunityPhases).evaluationPhaseData', [&$mout_simplify]);

                        $item = $emc->simplify("{$mout_simplify},opportunity,infos,evaluationFrom,evaluationTo,relatedAgents,agentRelations");
                        if($appeal_phase = $emc->appealPhase) {
                            $item->appealPhase = $appeal_phase;
                            $item->opportunity = $opportunity->simplify('id,isFirstPhase,isLastPhase,isReportingPhase,isLastReportingPhase,files,statusLabels,relatedAgents,agentRelations');
                            $item->opportunity->appealPhase = (object) $appeal_phase->jsonSerialize();
                            $item->opportunity->appealPhase->relatedAgents = $appeal_phase->relatedAgents;
                            $item->opportunity->appealPhase->agentRelations = $appeal_phase->agentRelations;
                            $item->opportunity->appealPhase->evaluationMethodConfiguration = (object) $appeal_phase->evaluationMethodConfiguration->jsonSerialize();
                            $item->opportunity->appealPhase->evaluationMethodConfiguration->relatedAgents = $appeal_phase->evaluationMethodConfiguration->relatedAgents;
                            $item->opportunity->appealPhase->evaluationMethodConfiguration->agentRelations = $appeal_phase->evaluationMethodConfiguration->agentRelations;
                        }
                        

                        $result[] = $item;
                    }
                }
            }

            $app->enableAccessControl();

            $value = $result;
        });

        $app->hook('entity(Opportunity).get(countEvaluations)', function(&$value) use ($app) {
            /** @var Opportunity $this */

            $this->enableCacheGetterResult('countEvaluations');

            $conn = $app->em->getConnection();

            $v = 0;
            if($result = $conn->fetchAll( "SELECT COUNT(*) AS qtd FROM evaluations WHERE opportunity_id = {$this->id}")){
                $v = $result[0]['qtd'];
            }

            $value = $v;

        });

        $app->hook('entity(Opportunity).get(lastCreatedPhase)', function(&$value) {
            /** @var Opportunity $this */

            $this->enableCacheGetterResult('lastCreatedPhase');

            $first_phase = $this->firstPhase;
            $value = Module::getLastCreatedPhase($first_phase);
        });

        $app->hook('entity(Opportunity).get(lastPhase)', function(&$value) use ($app) {
             /** @var Opportunity $this */

            $this->enableCacheGetterResult('lastPhase');

             $first_phase = $this->firstPhase;
             if(!$first_phase->id) {
                 return null;
             }

             if($this->isNew()) {
                 return $first_phase->lastPhase;
             }

             if($this->isLastPhase){
                return $this;
             }

             $class = Opportunity::class;

             $query = $app->em->createQuery("
                 SELECT o
                 FROM $class o
                 JOIN o.__metadata m WITH m.key = 'isLastPhase' AND m.value = '1'
                 WHERE o.parent = :parent"
                );

             $query->setMaxResults(1);
             $query->setParameters([
                 "parent" => $first_phase,
             ]);

             $value = $query->getOneOrNullResult();

             return;
        });

        $app->hook('entity(Registration).get(lastPhase)', function(&$value) use ($app) {
            /** @var Registration $this */
            $opportunity = $this->opportunity->isLastPhase ? $this->opportunity : $this->opportunity->lastPhase;
            $value = $app->repo('Registration')->findOneBy(['number' => $this->number, 'opportunity' => $opportunity]);
        });

        /**
         * Getters das fases de avaliação
         */

         $app->hook('entity(EvaluationMethodConfiguration).get(previousPhase)', function(&$value, $app) {
            /** @var EvaluationMethodConfiguration $this */

            $this->enableCacheGetterResult('previousPhase');

            $previous_phase = $this->opportunity;
            if ($previous_phase->isDataCollection) {
                $value = $previous_phase;
            } else {
                while(!$value && ($previous_phase = $previous_phase->previousPhase)) {
                    if ($emc = $previous_phase->evaluationMethodConfiguration) {
                        $value = $emc;
                    } elseif ($previous_phase->isDataCollection) {
                        $value = $previous_phase;
                    }
                }
            }
         });

         $app->hook('entity(EvaluationMethodConfiguration).get(nextPhase)', function(&$value) use($app) {
            /** @var EvaluationMethodConfiguration $this */

            $this->enableCacheGetterResult('nextPhase');

            $phase = $this->opportunity;
            while(!$value && ($phase = $phase->nextPhase)) {
                if ($phase->isDataCollection || $phase->isLastPhase) {
                    $value = $phase;
                } else if ($emc = $phase->evaluationMethodConfiguration) {
                    $value = $emc;
                }
            }
         });

        /**
         * Getters das inscrições
         */

        $app->hook('entity(Registration).get(previousPhase)', function(&$value) use($registration_repository) {
            /** @var Registration $this */

            $this->enableCacheGetterResult('previousPhase');

            if($this->previousPhaseRegistrationId) {
                $value = $registration_repository->find($this->previousPhaseRegistrationId);
            }

            if($value == $this) {
                $value = null;
            }
        });

        $app->hook('entity(Registration).get(nextPhase)', function(&$value) use($registration_repository) {
            /** @var Registration $this */

            $this->enableCacheGetterResult('nextPhase');

            if ($this->nextPhaseRegistrationId) {
                $value = $registration_repository->find($this->nextPhaseRegistrationId);
            }

            if($value == $this) {
                $value = null;
            }
        });

        $app->hook('entity(Registration).get(appealPhase)', function(&$value) use($registration_repository) {
            /** @var Registration $this */

            $this->enableCacheGetterResult('appealPhase');

            if($appeal_phase = $this->opportunity->appealPhase) {
                $value = $registration_repository->findOneBy([
                    'opportunity' => $appeal_phase, 
                    'number' => $this->number
                ]);
            }
        });

        /** @var \MapasCulturais\Connection $conn */
        $conn = $app->em->getConnection();

        $app->hook('entity(Registration).get(<<projectName|field_*>>)', function(&$value, $field_name) use($conn, $app) {
            /** @var Registration $this */

            if(!$this->canUser('viewPrivateData')) {
                return;
            }

            if(!isset($value) || empty($value) || $value == ""){
                $app->disableAccessControl();
                $reg = $conn->fetchAssociative("SELECT object_id, value FROM registration_meta WHERE key = '{$field_name}' AND object_id in (SELECT id FROM registration WHERE number = '{$this->number}')");

                if($reg && $this->id != $reg['object_id']){
                    $value = $reg['value'];
                    if($def = $this->getRegisteredMetadata($field_name)){
                        if(is_callable($def->unserialize)){
                            $registration = $app->repo('Registration')->find($reg['object_id']);
                            $cb = $def->unserialize;
                            $value = $cb($value, $registration, $def);
                        }
                    }
                }
                $app->enableAccessControl();
            }
        });

        $app->hook('entity(Registration).get(isFirstPhase)', function(&$value) {
            /** @var Registration $this */
            $value = $this->opportunity->isFirstPhase ? true : false;
        });

        $app->hook('entity(Registration).get(firstPhase)', function(&$value) use($registration_repository) {
            /** @var Registration $this */

            $this->enableCacheGetterResult('firstPhase');

            $opportunity = $this->opportunity;

            $value = $registration_repository->findOneBy(['opportunity' => $opportunity->firstPhase, 'number' => $this->number]);

        });

        $app->hook('entity(Registration).get(firstPhase)', function(&$value) use($registration_repository) {
            /** @var Registration $this */

            $this->enableCacheGetterResult('firstPhase');

            $opportunity = $this->opportunity;

            $value = $registration_repository->findOneBy(['opportunity' => $opportunity->firstPhase, 'number' => $this->number]);

        });

        /**
         * NOVAS ROTAS
         */

        $app->hook('API(opportunity.phases)', function() use($app) {
            /** @var \MapasCulturais\Controller $this */

            $opportunity = $app->repo('Opportunity')->find($this->data['@opportunity']);
            $result = $opportunity->phases;

            $this->json($result);
        });

        $app->hook('API(opportunity.phase)', function() use($app) {

            $opportunity = $app->repo('Opportunity')->find($this->data['@opportunity']);
            $result = $opportunity->simplify('summary','publishedRegistrations');
            $result->evaluationOpen = $opportunity->evaluationMethodConfiguration->evaluationOpen;
            $this->json($result);
        });

        // rota
        $app->hook('ALL(opportunity.syncRegistrations)', function() use($app, $self) {
            /** @var \MapasCulturais\Controllers\Opportunity $this */
            $opportunity = $this->requestedEntity;

            $opportunity->enqueueRegistrationSync();

           $app->enqueueOrReplaceJob(UpdateSummaryCaches::SLUG, [
                'opportunity' => $opportunity,
                'evaluationMethodConfiguration' => $opportunity->evaluationMethodConfiguration?: null
            ], '90 seconds');

            $this->finish(['message' => i::__('Sincronização das inscrições enfileirada para processamento em segundo plano')]);
        });

        // action para importar as inscrições da última fase concluida
        $app->hook('ALL(opportunity.importPreviousPhaseRegistrations)', function() use($app, $self) {
            /** @var \MapasCulturais\Controllers\Opportunity $this */
            $opportunity = $this->requestedEntity;

            $opportunity->enqueueRegistrationSync();

            $this->finish(['message' => i::__('Sincronização das inscrições enfileirada para processamento em segundo plano')]);
        });

        /**
         * Permissões
         */

        $app->hook('entity(Opportunity).canUser(view)', function($user, &$result){
            /** @var Opportunity $this */
            if($this->isOpportunityPhase && $this->status === -1){
                $result = true;
            }
        });

        $app->hook('entity(Registration).canUser(view)', function($user, &$result) use($app){
            /** @var Registration $this */
            if($result){
                return;
            }

            if($registration_id = $this->nextPhaseRegistrationId){
                $next_phase_registration = $app->repo('Registration')->find($registration_id);
                if ($next_phase_registration) {
                    $result = $next_phase_registration->canUser('view', $user);
                }
            }

            if(!$result && ($appeal_phase = $this->appealPhase)) {
                $result = $appeal_phase->canUser('view', $user);
            }
        });

        $app->hook('entity(Registration).canUser(viewPrivateData)', function($user, &$result) use($app, $registration_repository){
            /** @var Registration $this */
            if($result){
                return;
            }
            
            if(!$result) {
                if($appeal_phase = $this->opportunity->appealPhase) {

                    $registration_appeal_phase = $registration_repository->findOneBy([
                        'opportunity' => $appeal_phase, 
                        'number' => $this->number
                    ]);

                    if($registration_appeal_phase) {
                        $result = $registration_appeal_phase->canUser('viewPrivateData', $user);
                    }
                }
            }
        });


        /**
         * Demais hooks
         */

        /**
         * Serialização das oportunidades para incluir o nome da fase
         * por exemplo:
         * - Período de inscrição (quando não há uma fase de avaliação seguinte)
         * - Período de inscrição / Avaliação documental
         * - Nome da fase de coleta de dados
         * - Nome da fase de coleta de dados / Avaliação técnica
         * - Avaliação técnica (quando a fase de avaliação não sucede )
         */

        $app->hook('entity(Opportunity).jsonSerialize', function (&$data) {
            $current_phase = $this->firstPhase;
            $num = 0;
            $phases = $this->firstPhase->allPhases;

            foreach($phases as $current_phase) {
                if($current_phase->isDataCollection) {
                    $num++;
                }
                if($current_phase->evaluationMethodConfiguration) {
                    $num++;
                }

                if($current_phase->equals($this)) {
                    break;
                }
            }

            if($this->evaluationMethodConfiguration && $this->isDataCollection) {
                $phase_name = $this->isFirstPhase ? i::__('Período de inscrição') : $this->name;

                $num_1 = $num - 1;
                $name = "{$num_1}. {$phase_name} / {$num}. {$this->evaluationMethodConfiguration->name}";

            } else if ($this->evaluationMethodConfiguration) {
                $name = "{$num}. {$this->evaluationMethodConfiguration->name}";
            } else if ($this->isLastPhase) {
                $name = "{$num}. " . i::__('Publicação final do resultado');
            } else {
                $name = "{$num}. {$this->name}";
            }

            $data['phaseName'] = $name;
        });

        /** enfileira job para sincronização das inscrições em segundo plano */
        $app->hook('Entities\Opportunity::enqueueRegistrationSync', function($value, array $registrations = []) use($app) {
            
            // Não deve sincronizar as inscrições em fase de recurso
            if ($this->isAppealPhase) {
                return false;
            }

            $data = [
                'opportunity' => $this,
                'registrations' => $registrations
            ];

            $app->enqueueOrReplaceJob(Jobs\SyncPhaseRegistrations::SLUG, $data);
        });

        // sincroniza as inscrições da fase de acordo com o status da fase anterior
        $app->hook('Entities\Opportunity::syncRegistrations', function($value, array $registrations = []) use($app) {
            /** @var Opportunity $this */

            // Não deve sincronizar as inscrições da primeira fase ou fase de recurso
            if ($this->isFirstPhase || $this->isAppealPhase) {
                return false;
            }

            $this->checkPermission('@control');

            if ($app->config['app.log.syncRegistrations']) {
                $app->log->debug("Sincronizando inscrições da {$this->name} ({$this->id})");
            }
            $today = new \DateTime;

            $result = (object) [
                'imported' => [],
                'deleted' => []
            ];

            if ($this->registrationFrom <= $today || $this->isLastPhase || $this->isFinalReportingPhase) {

                $as_draft = $this->isDataCollection;

                $result->imported = $this->importPreviousPhaseRegistrations($as_draft, $registrations);
                $result->deleted = $this->removeOrphanRegistrations($registrations);
            }

            if($nextPhase = $this->nextPhase) {
                $nextPhase->syncRegistrations($registrations);
            }
            return $result;
        });

        // Remove as inscrições que não devem mais estar na fase
        $app->hook('Entities\Opportunity::removeOrphanRegistrations', function($value, array $registrations = []) use($app) {
            /** @var Opportunity $this */

            if ($this->isFirstPhase || $this->isLastPhase || $this->isAppealPhase) {
                return;
            }

            $this->checkPermission('@control');

            if ($app->config['app.log.syncRegistrations']) {
                $app->log->debug("  >> REMOVENDO inscrições órfãs da {$this->name} ({$this->id})");
            }

            $first_phase = $this->firstPhase;
            $previous_phase = $this->isFinalReportingPhase ? $this->lastPhase : $this->previousPhase;
            if ($app->config['app.log.syncRegistrations']) {
                $app->log->debug("  >>>>>>>  PREVIOUS  {$previous_phase->name} ({$previous_phase->id})");
            }

            $where_numbers = '';

            if ($registrations) {
                $numbers = [];
                foreach($registrations as $reg) {
                    if($reg instanceof Registration) {
                        $numbers[] = "'{$reg->number}'";
                    } else {
                        $numbers[] = "'" . ($reg['number'] ?? $reg) . "'";
                    }
                }

                $numbers = implode(',', $numbers);
                $where_numbers = "r1.number IN ({$numbers}) AND";
            }

            // para a última fase vão todas as inscrições que não estejam como rascunho
            $status = $this->isLastPhase || ($this->isReportingPhase && !$this->isFinalReportingPhase && !$previous_phase->isLastPhase) ? 'r2.status > 0' : 'r2.status = 10';

            $have_numbers_query = "";
            if(!$this->isReportingPhase) {
                $have_numbers_query = $where_numbers;
            }

            $dql = "
                    SELECT
                        r1
                    FROM
                        MapasCulturais\Entities\Registration r1
                    WHERE
                        r1.opportunity = :target_opportunity AND
                        $have_numbers_query
                        r1.number NOT IN (
                            SELECT
                                r2.number
                            FROM
                                MapasCulturais\Entities\Registration r2
                            WHERE
                                r2.opportunity = :previous_opportunity AND
                                {$status}
                        )
                    ORDER BY r1.id ASC
                ";


            $query = $app->em->createQuery($dql);

            $query->setMaxResults(1);

            $query->setParameters([
                'previous_opportunity' => $previous_phase,
                'target_opportunity' => $this,
            ]);

            $deleted_registrations = [];
            $count = 0;

            $app->disableAccessControl();
            while ($registration = $query->getOneOrNullResult()) {
                $count++;
                $deleted_registrations[] = $registration->number;
                if($app->config['app.log.syncRegistrations']) {
                    $app->log->debug("   >>> [{$count}] Removendo inscrição {$registration->number} da fase {$first_phase->name}/{$this->name} ({$this->id})");
                }
                $registration->delete(true);
                $app->em->clear();
            }
            $app->enableAccessControl();

            return $deleted_registrations;
        });

        // Importa as inscrições selecionadas da fase anterior
        $app->hook('Entities\Opportunity::importPreviousPhaseRegistrations', function($value, $as_draft = false, array $registrations = []) use($app, $self){
            /** @var Opportunity $this */

            // Não deve sincronizar as inscrições na primeira fase e na fase de recurso
            if ($this->isFirstPhase || $this->isAppealPhase) {
                return;
            }

            $lock_key = "importPreviousPhaseRegistrations:{$this->id}";
            $app->lock($lock_key, wait_for_unlock:10, expire_in:10 * MINUTE_IN_SECONDS);

            $this->checkPermission('@control');


            if($app->config['app.log.syncRegistrations']) {
                $app->log->debug("  >> IMPORTANDO inscrições da fase {$this->name} ({$this->id})");
            }

            $first_phase = $this->firstPhase;
            $previous_phase = $this->previousPhase;

            $where_numbers = '';
            if ($registrations) {
                $numbers = [];
                foreach($registrations as $current_phase_registration) {
                    if($current_phase_registration instanceof Registration) {
                        $numbers[] = "'{$current_phase_registration->number}'";
                    } else {
                        $numbers[] = "'" . ($current_phase_registration['number'] ?? $current_phase_registration) . "'";
                    }
                }

                $numbers = implode(',', $numbers);
                $where_numbers = "r1.number IN ({$numbers}) AND";
            }


            $app->disableAccessControl();

            $new_registrations = [];
            $count = 0;

            $repo = $app->repo('Registration');

            if ($this->isLastPhase) {
                $dql = "
                    SELECT
                        r1.id
                    FROM
                        MapasCulturais\Entities\Registration r1
                    WHERE
                        {$where_numbers}
                        r1.status > 0 AND
                        r1.opportunity = :previous_opportunity

                    ORDER BY r1.id ASC";

                $query = $app->em->createQuery($dql);

                $query->setParameters([
                    'previous_opportunity' => $first_phase,
                ]);

                $ids = $query->getSingleColumnResult();

                foreach($ids as $registration_id) {
                    $count++;

                    $registration = $repo->find($registration_id);
                    while($next_registration_phase = $registration->nextPhase) {
                        if($next_registration_phase->opportunity->isLastPhase) {
                            break;
                        } else{
                            $registration = $next_registration_phase;
                        }
                    }

                    if($app->config['app.log.syncRegistrations']) {
                        $app->log->debug("   >>> [{$count}] Importando inscrição {$registration->number} para a fase {$first_phase->name}/{$this->name} ({$this->id})");
                    }

                    if($current_phase_registration = $repo->findOneBy(['number' => $registration->number, 'opportunity' => $this])) {
                        $current_phase_registration->__skipQueuingPCacheRecreation = true;

                    } else {
                        $current_phase_registration = $current_phase_registration = $self->createPhaseRegistration($this, $registration);
                    }

                    $opp_phase = $registration->opportunity;
                    $phase = $opp_phase->evaluationMethodConfiguration ?: $opp_phase;
                    $label = $self->getRegistrationStatusLabels($registration->status)[1];
                    $label = str_replace('{PHASE_NAME}', $phase->name, $label);

                    $current_phase_registration->consolidatedResult = $label;
                    $current_phase_registration->score = $registration->score;

                    $methods = [
                        Registration::STATUS_DRAFT => 'setStatusToInvalid',
                        Registration::STATUS_SENT => 'setStatusToSent',
                        Registration::STATUS_APPROVED => 'setStatusToApproved',
                        Registration::STATUS_NOTAPPROVED => 'setStatusToNotApproved',
                        Registration::STATUS_WAITLIST => 'setStatusToWaitlist',
                        Registration::STATUS_INVALID => 'setStatusToInvalid',
                    ];

                    $method = $methods[$registration->status];

                    $current_phase_registration->$method();

                    $new_registrations[] = $current_phase_registration->number;

                    $app->em->clear();
                }


            } else if($this->isReportingPhase && !$this->isFinalReportingPhase && !$previous_phase->isLastPhase) {
                $dql = "
                    SELECT
                        r1
                    FROM
                        MapasCulturais\Entities\Registration r1
                    WHERE
                        r1.opportunity = :previous_opportunity AND
                        r1.status > 0 AND
                        r1.number NOT IN (
                            SELECT
                                r2.number
                            FROM
                                MapasCulturais\Entities\Registration r2
                            WHERE
                                r2.opportunity = :target_opportunity
                        )
                    ORDER BY r1.id ASC";

                $query = $app->em->createQuery($dql);
                $query->setMaxResults(1);

                $query->setParameters([
                    'previous_opportunity' => $previous_phase,
                    'target_opportunity' => $this
                ]);

                while ($registration = $query->getOneOrNullResult()) {
                    $count++;
                    if($app->config['app.log.syncRegistrations']) {
                        $app->log->debug("   >>> [{$count}] Importando inscrição {$registration->number} para a fase {$first_phase->name}/{$this->name} ({$this->id})");
                    }

                    $current_phase_registration = $self->createPhaseRegistration($this, $registration);

                    if(!$as_draft){
                        $current_phase_registration->send(false);
                    }

                    $new_registrations[] = $current_phase_registration->number;
                    $app->em->clear();

                }
            } else if($this->isFinalReportingPhase) {
                $last_phase = $this->lastPhase;
                $dql = "
                    SELECT
                        r1.id
                    FROM
                        MapasCulturais\Entities\Registration r1
                    WHERE
                        r1.status = 10 AND
                        r1.opportunity = :previous_opportunity

                    ORDER BY r1.id ASC";

                $query = $app->em->createQuery($dql);

                $query->setParameters([
                    'previous_opportunity' => $last_phase,
                ]);

                $ids = $query->getSingleColumnResult();
                
                foreach($ids as $registration_id) {
                    $count++;

                    $registration = $repo->find($registration_id);
                    while($next_registration_phase = $registration->nextPhase) {
                        if($next_registration_phase->opportunity->isFinalReportingPhase) {
                            break;
                        } else{
                            $registration = $next_registration_phase;
                        }
                    }

                    if ($app->config['app.log.syncRegistrations']) {
                        $app->log->debug("   >>> [{$count}] Importando inscrição {$registration->number} para a fase {$first_phase->name}/{$this->name} ({$this->id})");
                    }
                    
                    if($current_phase_registration = $repo->findOneBy(['number' => $registration->number, 'opportunity' => $this])) {
                        $current_phase_registration->__skipQueuingPCacheRecreation = true;

                    } else {
                        $current_phase_registration = $self->createPhaseRegistration($this, $registration);
                    }
                    
                    $opp_phase = $registration->opportunity;
                    $phase = $opp_phase->evaluationMethodConfiguration ?: $opp_phase;
                    $label = $self->getRegistrationStatusLabels($registration->status)[0];
                    
                    $current_phase_registration->consolidatedResult = $label;
                    $current_phase_registration->score = $registration->score;

                    $method = 'setStatusToDraft';
                    $current_phase_registration->$method();

                    $new_registrations[] = $current_phase_registration->number;

                    $app->em->clear();
                }
            } else {
                $dql = "
                    SELECT
                        r1
                    FROM
                        MapasCulturais\Entities\Registration r1
                    WHERE
                        r1.opportunity = :previous_opportunity AND
                        {$where_numbers}
                        r1.status = 10 AND
                        r1.number NOT IN (
                            SELECT
                                r2.number
                            FROM
                                MapasCulturais\Entities\Registration r2
                            WHERE
                                r2.opportunity = :target_opportunity
                        )
                    ORDER BY r1.id ASC";

                $query = $app->em->createQuery($dql);
                $query->setMaxResults(1);

                $query->setParameters([
                    'previous_opportunity' => $previous_phase,
                    'target_opportunity' => $this
                ]);


                while ($registration = $query->getOneOrNullResult()) {
                    $count++;
                    if ($app->config['app.log.syncRegistrations']) {
                        $app->log->debug("   >>> [{$count}] Importando inscrição {$registration->number} para a fase {$first_phase->name}/{$this->name} ({$this->id})");
                    }
                    $current_phase_registration = $self->createPhaseRegistration($this, $registration);

                    if(!$as_draft){
                        $current_phase_registration->send(false);
                    }

                    $new_registrations[] = $current_phase_registration->number;
                    $app->em->clear();

                }
            }


            $app->enqueueEntityToPCacheRecreation($this);
            $app->enableAccessControl();

            $app->unlock($lock_key);
            return $new_registrations;
        });


        $app->hook('entity(Registration).status(<<*>>),entity(Registration).remove:after', function() {
            /** @var Registration $this */
            if($this->skipSync) {
                return;
            }

            $current_phase = $this->opportunity;

            // Não deve sincronizar inscrições em fase de recursos
            if ($current_phase->isAppealPhase) {
                return;
            }

            if($next_phase = $current_phase->nextPhase){
                $next_phase->enqueueRegistrationSync([$this]);
                $last_phase = $current_phase->lastPhase;
                $last_phase->enqueueRegistrationSync([$this]);
            }
        });

        // muda o status de publicação dos oportunidades
        $app->hook('entity(<<*>>Opportunity).setStatus(1)', function(&$status) {
            if ($this->isOpportunityPhase) {
                $status = -1;
            }
        });

        $app->hook('controller(opportunity).getSelectFields', function(Opportunity $opportunity, array &$fields) use($app) {
            while($opportunity = $opportunity->parent){
                foreach($opportunity->registrationFieldConfigurations as $field){
                    if($field->fieldType == 'select'){
                        if(!isset($fields[$field->fieldName])){
                            $fields[$field->fieldName] = $field;
                        }
                    }
                }
            }
        });

        // envia e-mail para os aprovados na última fase
        $app->hook("entity(Opportunity).publishRegistrations:after", function () use ($app) {
            if (!$this instanceof Entities\ProjectOpportunity || !$this->isLastPhase) {
                return;
            }
            self::sendApprovalEmails($this);
            return;
        });

        // Não permite a criação de inscrições em fases fora da importaçao entre fases
        $app->hook('POST(registration.index):before', function() use($app) {
            $opportunity_id = $this->data['opportunityId'] ?? $this->data['opportunity'] ?? -1;
            $opportunity = $app->repo('Opportunity')->find($opportunity_id);

            if($opportunity->isOpportunityPhase){
                throw new Exceptions\PermissionDenied($app->user, $opportunity, 'register');
            }
        });

        /**
         * Validação das datas da fase de coleta de dados em relação às fases anterior e posterior
         */
        $app->hook('entity(Opportunity).validations', function(&$validations) {
            /** @var Opportunity $this */
            if ($this->firstPhase->isNew()) {
                return;
            }

            if ($next = $this->evaluationMethodConfiguration ?: $this->nextPhase ){
                if ($next->isLastPhase) {
                    $next_date_from = $next->publishTimestamp;
                    $next_date_to = $next->publishTimestamp;
                    $next_date_from_string = $next_date_from ? $next_date_from->format('Y-m-d H:i:s') : null;
                    $next_date_to_string = $next_date_to ? $next_date_to->format('Y-m-d H:i:s') : null;

                } else {
                    $next_date_from = $next->evaluationFrom ?: $next->registrationFrom;
                    $next_date_to = $next->evaluationTo ?: $next->registrationTo;
                    $next_date_from_string = $next_date_from ? $next_date_from->format('Y-m-d H:i:s') : null;
                    $next_date_to_string = $next_date_to ? $next_date_to->format('Y-m-d H:i:s') : null;
                }
            } else {
                $next_date_from = null;
                $next_date_to = null;
                $next_date_from_string = null;
                $next_date_to_string = null;
            }

            if ($this->isFirstPhase || !$this->previousPhase) {
                $previous = null;
                $previous_date_from = null;
                $previous_date_to = null;
                $previous_date_from_string = '';
                $previous_date_to_string = '';
            } else {
                $previous = $this->previousPhase->evaluationMethodConfiguration ?: $this->previousPhase;

                // Caso da primeira fase de monitoramento
                if($previous->isLastPhase) {
                    $previous_date_from = $previous->publishTimestamp;
                    $previous_date_to = $previous->publishTimestamp;
                } else {
                    $previous_date_from = $previous->evaluationFrom ?: $previous->registrationFrom;
                    $previous_date_to = $previous->evaluationTo ?: $previous->registrationTo;
                }

                $previous_date_from_string = $previous_date_from ? $previous_date_from->format('Y-m-d H:i:s') : null;
                $previous_date_to_string = $previous_date_to ? $previous_date_to->format('Y-m-d H:i:s') : null;

            }

            /**
             * Validação da data inicial
             */
            if ($next && $next_date_from_string) {
                $validations['registrationFrom']["\$value <= new DateTime('$next_date_from_string')"] = $next->isLastPhase ?
                    i::__('A data inicial deve ser anterior a data de publicação da última fase') :
                    i::__('A data inicial deve ser anterior a data de início da próxima fase');
            }
            if ($previous && $previous_date_from_string) {
                $validations['registrationFrom']["\$value >= new DateTime('$previous_date_from_string')"] = $previous->isFirstFase ?
                    i::__('A data inicial deve ser posterior a data de início do período de inscrições') :
                    i::__('A data inicial deve ser posterior a data de início da fase anterior');
            }

            /**
             * Validação da data final
             */
            if ($next && $next_date_to_string) {
                $shouldValidateRegistrationTo = (!$this->isContinuousFlow) || ($this->isContinuousFlow && $this->hasEndDate);
                
                if($shouldValidateRegistrationTo){
                    $validations['registrationTo']["\$value <= new DateTime('$next_date_to_string')"] = $next->isLastPhase ? 
                        i::__('A data final deve ser anterior a data de publicação da última fase') :
                        i::__('A data final deve ser anterior a data de término da próxima fase');
                }
            }
            if ($previous && $previous_date_to_string) {
                $validations['registrationTo']["\$value >= new DateTime('$previous_date_to_string')"] = $previous->isFirstFase ?
                    i::__('A data final deve ser posterior a data de término do período de inscrições') :
                    i::__('A data final deve ser posterior a data de término da fase anterior');
            }

            /**
             * Validação da data de publicação dos resultados
             */
            if ($this->publishTimestamp) {

                if ($this->isLastPhase && $previous_date_to_string) {
                    $validations['publishTimestamp']["\$value >= new DateTime('$previous_date_to_string')"] = i::__('A data de publicação final do resultado deve ser posterior a data de término da fase anterior');
                } else if($date_to_string = $this->registrationTo ? $this->registrationTo->format('Y-m-d H:i:s') : null ) {
                    $validations['publishTimestamp']["\$value >= new DateTime('$date_to_string')"] = i::__('A data de publicação do resultado deve ser posterior a data de término da fase');
                } else if($previous_date_to_string) {
                    $validations['publishTimestamp']["\$value >= new DateTime('$previous_date_to_string')"] = i::__('A data de publicação do resultado deve ser posterior a data de término da fase anterior');

                }
            }
        });

        /**
         * Validação das datas da fase de avaliação em relação às fases anterior e posterior
         */
        $app->hook('entity(EvaluationMethodConfiguration).validations', function(&$validations) {
            if($previous_phase = $this->previousPhase){
                $previous_date_from = ($previous_phase instanceof Opportunity) ? $previous_phase->registrationFrom : $previous_phase->evaluationFrom;
                
                if($this->evaluationFrom < $previous_date_from) {
                    $previous_date_from_string = $previous_date_from->format('Y-m-d H:i:s');
                    $validations['evaluationFrom']["\$value >= new DateTime('$previous_date_from_string')"] = i::__('A data inicial deve ser maior ou igual a data de inicio da fase anterior');
                }

                $previous_date_to = ($previous_phase instanceof Opportunity) ? $previous_phase->registrationTo : $previous_phase->evaluationTo;
                
                if($this->evaluationTo < $previous_date_to) {
                    $previous_date_to_string = $previous_date_to->format('Y-m-d H:i:s');
                    $validations['evaluationTo']["\$value >= new DateTime('$previous_date_to_string')"] = i::__('A data final deve ser maior ou igual a data de término da fase anterior');
                }
            }

            if (!$this->id) {
                $next_phase = $this->opportunity->lastPhase;
                $error_message = i::__('A data final deve ser menor que a data de final de publicação dos resultados');
            } else {
                $next_phase = $this->nextPhase;
                $error_message = i::__('A data final deve ser menor que a data de término da próxima fase');
            }

            $date_to = null;

            if($next_phase instanceof Opportunity) {
                $date_to = $next_phase->isLastPhase ? $next_phase->publishTimestamp :  $next_phase->registrationTo;
            } else if(is_object($next_phase)) {
                $date_to = $next_phase->evaluationTo;
            }

            if($date_to) {
                $date_to = $date_to->format('Y-m-d H:i:s');
                $validations['evaluationTo']["\$value <= new DateTime('$date_to')"] = $error_message;
            }
        });

        /** Define os termos das fases como igual aos termos da primeira fase */
        $app->hook('entity(Opportunity).set(<<parent|parentId>>)', function ($parent) use($app) {
            /** @var Opportunity $this */
            if ($parent) {
                if(is_numeric($parent)){
                    $parent = $app->repo('Opportunity')->find($parent);
                }
                $this->terms = (array) $parent->terms;
            }
        });

        /**
         * Corrige a propriedade opportunity da fase de avaliação antes da criação.
         * A fase de avaliação é sempre criada para a última fase (opportunity) da oportunidade,
         * e no caso desta já possuir uma fase de avaliação, uma nova fase (opportunity) sem coleta de dados
         * é criada para "abrigar" a fase de avaliaçao.
         */
        $app->hook('entity(EvaluationMethodConfiguration).insert:before', function () {
            $phase = null;
            $phases = $this->opportunity->allPhases;

            if($this->opportunity && $this->opportunity->isAppealPhase) {
                return;
            }

            // procura a última fase (opportunity) sem método de avaliação/
            // que não seja a fase de publicação de resultado.
            for($i = count($phases) -1; $i >=0; $i--) {
                $_phase = $phases[$i];
                if(!$_phase->isLastPhase && !$_phase->evaluationMethodConfiguration) {
                    $phase = $_phase;
                    break;
                }
            }

            if(!$phase) {
                // se entrou aqui é pq todas as fases tem método de avaliação,
                // então precisamos criar uma nova fase (opportunity) para abrigar
                // a nova fase de avaliação
                $class = get_class($this->opportunity);

                $first_phase = $this->opportunity;

                $phase = new $class;
                $phase->status = -1;
                $phase->parent = $first_phase;
                $phase->name = $this->name;
                $phase->type = $first_phase->type;
                $phase->isOpportunityPhase = true;
                $phase->isDataCollection = '0';
                $phase->save(true);
            }
            $this->opportunity = $phase;
        });

        
        $app->hook('entity(EvaluationMethodConfiguration).remove:after', function () use($app) {
            /** @var EvaluationMethodConfiguration $this */
            
            /** @var Opportunity */
            $opportunity = $this->opportunity;
            
            /** @var Opportunity */
            $next_phase = $opportunity->nextPhase;
            
            /** @var Opportunity */
            $previous_phase = $opportunity->previousPhase;

            // se a próxima fase for a última fase e a fase atual não for uma fase de coleta de dados, apaga a fase atual
            if ($next_phase->isLastPhase){
                if (!$opportunity->isDataCollection) {
                    $opportunity->destroy();
                    $previous_phase->fixNextPhaseRegistrationIds();
                }

                return;
            }   

            // se a próxima fase não for uma fase de coleta de dados, nem for a última fase e tiver uma fase de avaliação, 
            // transfere a fase de avaliação para a oportunidade que estava vinculada a fase de avaliação deletada
            if(!$next_phase->isDataCollection  && $next_phase->evaluationMethodConfiguration) {
                $emc = $next_phase->evaluationMethodConfiguration;
                $emc->opportunity = $opportunity;
                $opportunity->evaluationMethodConfiguration = $emc;
                $emc->save(true);
                $opportunity->save(true);

                /** @var Connection */
                $conn = $app->em->getConnection();

                // apaga todas as avaliações das inscrições da oportunidade vinculada
                $conn->executeQuery("
                    DELETE FROM registration_evaluation 
                    WHERE registration_id IN (
                        SELECT id FROM registration 
                        WHERE opportunity_id = {$opportunity->id}
                    )");

                // transfere as avaliações da próxima fase para a fase atual
                
                $conn->executeQuery("
                    WITH regs AS (
                        SELECT r.id AS current_id, prev.value::INTEGER as prev_id 
                        FROM registration r
                            JOIN registration_meta prev ON 
                                prev.key = 'previousPhaseRegistrationId' AND 
                                prev.object_id = r.id
                        WHERE r.opportunity_id = {$next_phase->id}
                    )
                    UPDATE registration_evaluation
                    SET registration_id = regs.prev_id
                    FROM regs
                    WHERE registration_evaluation.registration_id = regs.current_id
                ");

                $next_phase->delete(true);
            }

        });

        /** Adiciona o isFirstPhase ao requestedEntity */
        $app->hook('view.requestedEntity(Opportunity).result', function(&$entity) {
            $entity['isFirstPhase'] = !isset($entity['parent']);
        });

        /** Adiciona o isFirstPhase ao propertiesMetadata  */
        $app->hook('entity(Opportunity).propertiesMetadata', function (&$result) {
            $result['isFirstPhase'] = [
                'label' => i::__('Indica se o objeto é a primeira fase da oportunidade'),
                'isMetadata' => true,
                'isEntityRelation' => false,
                'required' => false,
                'type' => 'boolean',
                'isReadonly' => true,
            ];

            $result['phaseName'] = [
                'label' => i::__('Nome da fase para exibição do usuário, considerando a fase de avaliação conjunta'),
                'isMetadata' => true,
                'isEntityRelation' => false,
                'required' => false,
                'type' => 'string',
                'isReadonly' => true,
            ];
        });

        /** Adiciona o summary ao propertiesMetadata  */
        $app->hook('entity(<<Opportunity|EvaluationMethodConfiguration>>).propertiesMetadata', function (&$result) {
            $result['summary'] = [
                'label' => i::__('Resumo dos status da fase'),
                'isMetadata' => true,
                'isEntityRelation' => false,
                'required' => false,
                'type' => 'object',
                'isReadonly' => true,
            ];
        });

        // hooks específicos para os novos temas
        if ($app->view->version >= 2) {
            // cria a fase de publicaçao de resultado na criação de novas oportunidades
            $app->hook('entity(Opportunity).insert:after', function() use ($app) {
                /** @var Opportunity $this */
                if ($this->parent) {
                    return;
                }

                $class = get_class($this);

                /** @var Opportunity $last_phase */
                $last_phase = new $class;
                $last_phase->status = -1;
                $last_phase->parent = $this;
                $last_phase->name = i::__('Publicação final do resultado');
                $last_phase->type = $this->type;
                $last_phase->isLastPhase = true;
                $last_phase->isOpportunityPhase = true;
                $last_phase->isDataCollection = '0';
                $last_phase->save(true);
            });

            // atualiza as datas da oportunidade auxiliar das fases de avaliação sem coleta de dados
            $app->hook('entity(EvaluationMethodConfiguration).save:finish', function($flush) use ($app) {
                /** @var EvaluationMethodConfiguration $this */
                $opportunity = $this->opportunity;
                if (!$opportunity->isDataCollection && ($opportunity->registrationFrom != $this->evaluationFrom || $opportunity->registrationTo != $this->evaluationTo)) {
                    $opportunity->registrationFrom = $this->evaluationFrom;
                    $opportunity->registrationTo = $this->evaluationTo;
                    $opportunity->save($flush);
                }
            });

            // remove a oportunidade auxiliar da fases de avaliação sem coleta de dados
            $app->hook('entity(EvaluationMethodConfiguration).remove:after', function() use ($app) {
                /** @var EvaluationMethodConfiguration $this */
                $app->em->clear();
                $opportunity = $app->repo('Opportunity')->find($this->opportunity->id);

                if (!$opportunity->isDataCollection) {
                    $opportunity->destroy(true);
                }
            });

            // se não for enviado os parâmetros opportunity, previousPhaseRegistrationId ou id, a API de registration só deve retornar inscrições da primeira fase
            $app->hook('ApiQuery(Registration).params', function(&$params) {
                /** @var ApiQuery $this */
                if(!$this->parentQuery && !isset($params['opportunity']) && !isset($params['previousPhaseRegistrationId']) && !isset($params['id'])) {
                    $params['previousPhaseRegistrationId'] = API::NULL();
                }
            });

            // Adiciona os proponentes, as faixas e as categorias para as novas fases de coleta de dados criadas
            $app->hook('entity(Opportunity).insert:after', function() use ($app) {
                /** @var Opportunity $this */
                if($this->parent && $this->isDataCollection) {
                    $this->registrationCategories = $this->parent->registrationCategories;
                    $this->registrationProponentTypes = $this->parent->registrationProponentTypes;
                    $this->registrationRanges = $this->parent->registrationRanges;
                    $this->save(true);
                }
            });

            $app->hook('entity(Opportunity).insert:after', function() use ($app) {
                /** @var Opportunity $this */
                if($this->parent && $this->firstPhase->isContinuousFlow) {
                    $this->isContinuousFlow = true;
                    $this->save(true);
                }
            });

            $app->hook('entity(Registration).insert:after', function() use($app){
                /** @var Registration $this */

                $app->disableAccessControl();

                if ($this->previousPhase) {
                    $this->range = $this->previousPhase->range;
                    $this->proponentType = $this->previousPhase->proponentType;
                    if($this->previousPhase->score) {
                        $this->score = $this->previousPhase->score;
                    }
                    if($this->previousPhase->eligible) {
                        $this->eligible = $this->previousPhase->eligible;
                    }
                    $this->save(true);
                }

                $app->enableAccessControl();
            });

            $app->hook('entity(Registration).save:after', function() use($app){
                /** @var Registration $this */

                $app->disableAccessControl();

                if( $this->nextPhase){
                    $this->nextPhase->range = $this->range;
                    $this->nextPhase->proponentType = $this->proponentType;
                    if($this->score) {
                        $this->nextPhase->score = $this->score;
                    }
                    if($this->eligible) {
                        $this->nextPhase->eligible = $this->eligible;
                    }

                    $this->nextPhase->save(true);
                }
                $app->enableAccessControl();
            });
        }
    }

    function register () {
        $app = App::i();

        // registra o job que faz a sincronização das inscrições em background no servidor
        $app->registerJobType(new Jobs\SyncPhaseRegistrations(Jobs\SyncPhaseRegistrations::SLUG));

        $def__is_opportunity_phase = new Definitions\Metadata('isOpportunityPhase', ['label' => \MapasCulturais\i::__('Is a opportunity phase?')]);
        $def__previous_phase_imported = new Definitions\Metadata('previousPhaseRegistrationsImported', ['label' => \MapasCulturais\i::__('Previous phase registrations imported')]);

        $app->registerMetadata($def__is_opportunity_phase, 'MapasCulturais\Entities\Opportunity');
        $app->registerMetadata($def__previous_phase_imported, 'MapasCulturais\Entities\Opportunity');

        $def__prev = new Definitions\Metadata('previousPhaseRegistrationId', ['label' => \MapasCulturais\i::__('Previous phase registration id')]);
        $def__next = new Definitions\Metadata('nextPhaseRegistrationId', ['label' => \MapasCulturais\i::__('Next phase registration id')]);

        $app->registerMetadata($def__prev, 'MapasCulturais\Entities\Registration');
        $app->registerMetadata($def__next, 'MapasCulturais\Entities\Registration');

        // Last phase metadata
        $this->registerOpportunityMetadata('isLastPhase', [
            'label' => i::__('Indica se a oportunidade é a última fase da oportunidade'),
            'type' => 'boolean',
            'default' => false
        ]);

        $this->registerOpportunityMetadata("isDataCollection", [
            'label'=> "Define se é uma oportunidade de coleta de dados",
            'type'=>'bool',
            'default'=> true,
        ]);

        $this->registerOpportunityMetadata("registrationsOutdated", [
            'label'=> "Indica que as inscrições da fase não estão atualizadas",
            'type' => 'bool',
            'default'=> false,
        ]);

        $this->registerEvauationMethodConfigurationMetadata('statusLabels', [
            'label' => i::__('Label dos status das fases de avaliações'),
            'type' => 'array',
        ]);

        $this->registerOpportunityMetadata('statusLabels', [
            'label' => i::__('Label dos status das fases de avaliações'),
            'type' => 'array',
            'unserialize' => function($value, $entity) use($app) {
                if(!$value) {
                    if(!$entity instanceof Opportunity) {
                        $entity = $app->repo('Opportunity')->find($entity->id);
                    }

                    if($entity->evaluationMethodConfiguration) {
                        return $entity->evaluationMethodConfiguration->defaultStatuses;
                    }

                    return $entity->defaultStatuses;
                }

                return json_decode($value, true);
            }
        ]);
    }


    function importPreviousPhaseRegistrations(Opportunity $previous_phase, Opportunity $target_opportunity, $as_draft = false) {
        $target_opportunity->importPreviousPhaseRegistrations($as_draft);
    }

    static function sendApprovalEmails(Opportunity $opportunity)
    {
        $app = App::i();
        $registrations = $app->repo("Registration")->findBy([
            "opportunity" => $opportunity,
            "status" => Registration::STATUS_APPROVED
        ]);
        foreach ($registrations as $registration) {
            $template = "opportunityphases/selected-communication.html";
            $params = [
                "siteName" => $app->siteName,
                "user" => $registration->owner->name,
                "baseUrl" => $registration->singleUrl,
                "opportunityId" => $opportunity->id,
                "opportunityTitle" => $opportunity->firstPhase->name,
                "registrationNumber" => $registration->number,
                "registrationUrl" => $registration->singleUrl
            ];
            $email_params = [
                "from" => $app->config["mailer.from"],
                "to" => ($registration->owner->emailPrivado ??
                         $registration->owner->emailPublico ??
                         $registration->ownerUser->email),
                "subject" => sprintf(i::__("Aviso sobre a sua inscrição na " .
                                           "oportunidade %s"),
                                           $opportunity->firstPhase->name),
                "body" => $app->renderMustacheTemplate($template, $params)
            ];
            if (!isset($email_params["to"])) {
                return;
            }
            $app->createAndSendMailMessage($email_params);
        }
        return;
    }

    function createPhaseRegistration(Opportunity $opportunity, Registration $registration) {
        $current_phase_registration = new Registration;
    
        $current_phase_registration->owner = $registration->owner->refreshed();
        $current_phase_registration->opportunity = $opportunity->refreshed();
        $current_phase_registration->category = $registration->category;
        $current_phase_registration->range = $registration->range;
        $current_phase_registration->proponentType = $registration->proponentType;
        $current_phase_registration->number = $registration->number;
        $current_phase_registration->previousPhaseRegistrationId = $registration->id;
    
        $current_phase_registration->__skipQueuingPCacheRecreation = true;
        $current_phase_registration->save(true);
    
        $registration->nextPhaseRegistrationId = $current_phase_registration->id;
        $registration->__skipQueuingPCacheRecreation = true;
        $registration->save(true);
    
        return $current_phase_registration;
    }

    function getRegistrationStatusLabels($status) {
        $labels = [
            Registration::STATUS_DRAFT => [i::__('Não enviou inscrição'), i::__('Não enviou inscrição em "{PHASE_NAME}"')],
            Registration::STATUS_SENT => [i::__('Pendente'), i::__('Pendente em "{PHASE_NAME}"')],
            Registration::STATUS_APPROVED => [i::__('Selecionada'), i::__('Selecionada em "{PHASE_NAME}"')],
            Registration::STATUS_NOTAPPROVED => [i::__('Não selecionada'), i::__('Não selecionada em "{PHASE_NAME}"')],
            Registration::STATUS_WAITLIST => [i::__('Suplente'), i::__('Suplente em "{PHASE_NAME}"')],
            Registration::STATUS_INVALID => [i::__('Inválida'), i::__('Inválida em "{PHASE_NAME}"')],
        ];

        return $labels[$status];
    }
}
