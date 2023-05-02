<?php
namespace OpportunityPhases;

use MapasCulturais\ApiQuery;
use MapasCulturais\App;
use MapasCulturais\Definitions;
use MapasCulturais\Entities;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\Registration;
use MapasCulturais\Exceptions;
use MapasCulturais\i;
use PHPUnit\Util\Annotation\Registry;

class Module extends \MapasCulturais\Module{

    /**
     * Retorna o oportunidade principal
     *
     * @return Opportunity
     */
    static function getBaseOpportunity(Opportunity $opportunity = null){
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
                $value = $first_phase->lastPhase->previousPhase;
                return;
            }

            $class = Opportunity::class;
            $query = $app->em->createQuery("
                SELECT o 
                FROM $class o 
                WHERE 
                    o.id = :parent OR
                    (o.parent = :parent AND o.registrationFrom < (SELECT this.registrationFrom FROM $class this WHERE this.id = :this))
                ORDER BY o.registrationFrom DESC");

            $query->setMaxResults(1);
            $query->setParameters([
                "parent" => $first_phase,
                "this" => $this,
            ]);

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
                $value = $first_phase->lastPhase;
                return;
            }
            $class = Opportunity::class;
            $query = $app->em->createQuery("
                SELECT o 
                FROM $class o 
                WHERE 
                    o.parent = :parent AND 
                    (
                        o.registrationFrom > (SELECT this1.registrationFrom FROM $class this1 WHERE this1.id = :this) OR 
                        (o.registrationFrom IS NULL AND o.publishTimestamp > (SELECT this2.registrationFrom FROM $class this2 WHERE this2.id = :this))
                    )
                ORDER BY o.registrationFrom ASC");

            $query->setMaxResults(1);
            $query->setParameters([
                "parent" => $first_phase,
                "this" => $this,
            ]);

            $value = $query->getOneOrNullResult();
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
            $class = Opportunity::class;
            $query = $app->em->createQuery("
                SELECT o 
                FROM $class o 
                WHERE 
                    o.parent = :parent AND 
                    o.registrationFrom > (SELECT this.registrationFrom FROM $class this WHERE this.id = :this) 
                ORDER BY o.registrationFrom ASC");

            $query->setParameters([
                "parent" => $first_phase,
                "this" => $this,
            ]);

            $value = $query->getResult();
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
            $values = [$first_phase];
            $class = Opportunity::class;
            $query = $app->em->createQuery("
                SELECT o 
                FROM $class o 
                WHERE o.parent = :parent 
                ORDER BY o.registrationFrom ASC, o.id ASC");

            $query->setParameters([
                "parent" => $first_phase,
            ]);

            $last_phase = null;
            foreach($query->getResult() as $opp) {
                if($opp->isLastPhase) {
                    $last_phase = $opp;
                } else {
                    $values[] = $opp;
                }
            }

            if($last_phase) {
                $values[] = $last_phase;
            }

        });

        /**
         * retorna a lista de fases de coleta de dados e avaliação
         */
        $app->hook('entity(Opportunity).get(phases)', function (&$value) use($app) {
            /** @var Opportunity $this */
            $result = [];
            $app->disableAccessControl();

            $firstPhase = $this->firstPhase;
            
            $mout_symplyfy = "id,name,summary";
            if($opportunity_phases = $firstPhase->allPhases){
                foreach($opportunity_phases as $key => $opportunity){
                    $emc = $opportunity->evaluationMethodConfiguration;
                    if($opportunity->isDataCollection || $opportunity->isFirstPhase || $opportunity->isLastPhase){
                        $item = $opportunity->simplify("{$mout_symplyfy},type,publishedRegistrations,publishTimestamp,registrationFrom,registrationTo,isFirstPhase,isLastPhase");
                        
                        if($emc){
                            $item->evaluationMethodConfiguration = $emc->simplify("id,name,evaluationFrom,evaluationTo");
                        }
                        
                        $result[] = $item;
                    }

                    if($emc){

                        if($opportunity->isDataCollection){
                            $mout_symplyfy.=",ownerId";
                        }

                        $result[] = $emc->simplify("{$mout_symplyfy},opportunity,infos,evaluationFrom,evaluationTo");
                    }
                }
            }
            $app->enableAccessControl();
            
            $value = $result;
        });

        $app->hook('entity(Opportunity).get(countEvaluations)', function(&$value) use ($app) {
            /** @var Opportunity $this */
            $conn = $app->em->getConnection();

            $v = 0;
            if($result = $conn->fetchAll( "SELECT COUNT(*) AS qtd FROM evaluations WHERE opportunity_id = {$this->id}")){
                $v = $result[0]['qtd'];
            }

            $value = $v;
            
        });
    
        $app->hook('entity(Opportunity).get(lastCreatedPhase)', function(&$value) {
            /** @var Opportunity $this */
            $first_phase = $this->firstPhase;
            $value = Module::getLastCreatedPhase($first_phase);
        });

        $app->hook('entity(Opportunity).get(lastPhase)', function(&$value) use ($app) {
             /** @var Opportunity $this */
             $first_phase = $this->firstPhase;
             if(!$first_phase->id) {
                 return;
             }

             if($this->isNew()) {
                 $value = $first_phase->lastPhase;
                 return;
             }

             if($this->isLastPhase){
                return $this;
             }

             $class = Opportunity::class;
             $meta_class = $this->metadataClassName;

             $query = $app->em->createQuery("
                 SELECT o 
                 FROM $class o 
                 JOIN $meta_class m WITH m.key = 'isLastPhase'
                 WHERE 
                     o.parent = :parent AND
                     m.value = '1'"
                );
 
             $query->setMaxResults(1);
             $query->setParameters([
                 "parent" => $first_phase,
             ]);
 
             $value = $query->getOneOrNullResult();
        });

        /**
         * Getters das fases de avaliação
         */

         $app->hook('entity(EvaluationMethodConfiguration).get(previousPhase)', function(&$value) {
            /** @var EvaluationMethodConfiguration $this */
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
            if($this->previousPhaseRegistrationId) {
                $value = $registration_repository->find($this->previousPhaseRegistrationId);
            }

            if($value == $this) {
                $value = null;
            }
        });

        $app->hook('entity(Registration).get(nextPhase)', function(&$value) use($registration_repository) {
            /** @var Registration $this */
            if ($this->nextPhaseRegistrationId) {
                $value = $registration_repository->find($this->nextPhaseRegistrationId);
            }

            if($value == $this) {
                $value = null;
            }
        });

        $app->hook('entity(Registration).get(<<projectName|field_*>>)', function(&$value, $field_name) use($app) {
            /** @var Registration $this */

            if(!$this->canUser('viewPrivateData')) {
                return;
            }
            if(empty($value) && ($previous_phase = $this->previousPhase)){
                $previous_phase->registerFieldsMetadata();

                $app->disableAccessControl();
                $value = $previous_phase->$field_name;
                $app->enableAccessControl();
            }
        });

        $app->hook('entity(Registration).get(firstPhase)', function(&$value) use($registration_repository) {
            /** @var Registration $this */
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
            if($this->isOpportunityPhase && $this->status === -1){
                $result = true;
            }
        });

        $app->hook('entity(Registration).canUser(view)', function($user, &$result) use($app){
            if($result){
                return;
            }

            if($registration_id = $this->nextPhaseRegistrationId){
                $next_phase_registration = $app->repo('Registration')->find($registration_id);
                if ($next_phase_registration) {
                    $result = $next_phase_registration->canUser('view', $user);
                }
            }
        });


        /**
         * Demais hooks
         */

        /** enfileira job para sincronização das inscrições em segundo plano */
        $app->hook('Entities\Opportunity::enqueueRegistrationSync', function($value, array $registrations = []) use($app) {
            $data = [
                'opportunity' => $this,
                'registrations' => $registrations
            ];

            $app->enqueueJob(Jobs\SyncPhaseRegistrations::SLUG, $data);
        });

        // sincroniza as inscrições da fase de acordo com o status da fase anterior
        $app->hook('Entities\Opportunity::syncRegistrations', function($value, array $registrations = []) use($app) {
            /** @var Opportunity $this */

            if ($this->isFirstPhase) {
                return false;
            }

            $this->checkPermission('@control');

            $app->log->debug("Sincronizando inscrições da {$this->name} ({$this->id})");

            $today = new \DateTime;

            $result = (object) [
                'imported' => [],
                'deleted' => []
            ];

            if ($this->registrationFrom <= $today || $this->isLastPhase) {

                $as_draft = $this->isDataCollection;
                
                $result->imported = $this->importPreviousPhaseRegistrations($as_draft, $registrations);
                $result->deleted = $this->removeOrphanRegistrations($registrations);
            }

            return $result;
        });

        // Remove as inscrições que não devem mais estar na fase
        $app->hook('Entities\Opportunity::removeOrphanRegistrations', function($value, array $registrations = []) use($app) {
            /** @var Opportunity $this */

            if ($this->isFirstPhase) {
                return;
            }

            $this->checkPermission('@control');

            $app->log->debug("  >> REMOVENDO inscrições órfãs da {$this->name} ({$this->id})");

            $first_phase = $this->firstPhase;
            $previous_phase = $this->previousPhase;

            $where_ids = '';

            if ($registrations) {
                $ids = [];
                foreach($registrations as $reg) {
                    if($reg instanceof Registration) {
                        $ids[] = $reg->id;
                    } else {
                        $ids[] = $reg['id'] ?? $reg;   
                    }
                }

                $ids = implode(',', $ids);
                $where_ids = "r1.id IN ({$ids}) AND";
            } 

            // para a última fase vão todas as inscrições que não estejam como rascunho
            $status = $this->isLastPhase ? 'r2.status > 0' : 'r2.status = 10';

            $dql = "
                SELECT
                    r1
                FROM
                    MapasCulturais\Entities\Registration r1
                WHERE
                    r1.opportunity = :target_opportunity AND
                    $where_ids
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
                $app->log->debug("   >>> [{$count}] Removendo inscrição {$registration->number} da fase {$first_phase->name}/{$this->name} ({$this->id})");
                $registration->delete(true);
                $app->em->clear();
            }
            $app->enableAccessControl();

            return $deleted_registrations;
        });

        // Importa as inscrições selecionadas da fase anterior
        $app->hook('Entities\Opportunity::importPreviousPhaseRegistrations', function($value, $as_draft = false, array $registrations = []) use($app){
            /** @var Opportunity $this */

            if ($this->isFirstPhase) {
                return;
            }

            $this->checkPermission('@control');

            $app->log->debug("  >> IMPORTANDO inscrições da fase {$this->name} ({$this->id})");

            $first_phase = $this->firstPhase;
            $previous_phase = $this->previousPhase;
            

            $where_ids = '';
            if ($registrations) {
                $ids = [];
                foreach($registrations as $reg) {
                    if($reg instanceof Registration) {
                        $ids[] = $reg->id;
                    } else {
                        $ids[] = $reg['id'] ?? $reg;   
                    }
                }

                $ids = implode(',', $ids);
                $where_ids = "r1.id IN ({$ids}) AND";
            } 

            // para a última fase vão todas as inscrições que não estejam como rascunho
            $status = $this->isLastPhase ? 'r1.status > 0' : 'r1.status = 10';

            $dql = "
                SELECT
                    r1
                FROM
                    MapasCulturais\Entities\Registration r1
                WHERE
                    r1.opportunity = :previous_opportunity AND
                    {$where_ids}
                    {$status} AND
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

            $new_registrations = [];
            $count = 0;
            
            $app->disableAccessControl();
            while ($registration = $query->getOneOrNullResult()) {
                $count++;

                $app->log->debug("   >>> [{$count}] Importando inscrição {$registration->number} para a fase {$first_phase->name}/{$this->name} ({$this->id})");

                $reg = new Registration;
                $reg->__skipQueuingPCacheRecreation = true;
                $reg->owner = $registration->owner->refreshed();
                $reg->opportunity = $this->refreshed();
                $reg->category = $registration->category;
                $reg->number = $registration->number;

                $reg->previousPhaseRegistrationId = $registration->id;
                $reg->save(true);

                if($this->isLastPhase) {
                    $methods = [
                        Registration::STATUS_DRAFT => 'setStatusToDraft',
                        Registration::STATUS_SENT => 'setStatusToSent',
                        Registration::STATUS_APPROVED => 'setStatusToApproved',
                        Registration::STATUS_NOTAPPROVED => 'setStatusToNotApproved',
                        Registration::STATUS_WAITLIST => 'setStatusToWaitlist',
                        Registration::STATUS_INVALID => 'setStatusToInvalid',
                    ];

                    $method = $methods[$registration->status];
                    $reg->consolidatedResult = $registration->consolidatedResult;
                    $reg->$method();
                } else if(!$as_draft){
                    $reg->send();
                }

                $registration->__skipQueuingPCacheRecreation = true;
                $registration->nextPhaseRegistrationId = $reg->id;

                $registration->save(true);

                $new_registrations[] = $reg->number;

                $app->em->clear();
            }

            $app->enqueueEntityToPCacheRecreation($this);
            $app->enableAccessControl();

            return $new_registrations;
        });

        $app->hook('entity(Registration).status(<<*>>),entity(Registration).remove:after>>', function() {
            /** @var Registration $this */
            $current_phase = $this->opportunity;
            if($next_phase = $current_phase->nextPhase){
                $next_phase->enqueueRegistrationSync();
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
            if($this->parent) {
                $previous = $this->previousPhase;
                $prev_em = $previous->evaluationMethodConfiguration;

                if($prev_em) {
                    $previous_date = max($prev_em->evaluationTo, $previous->registrationTo);
                } else {
                    $previous_date = $previous->registrationTo;
                }

                if($previous_date) {
                    $previous_date = $previous_date->format('Y-m-d H:i:s');
    
                    $validations['registrationFrom']["\$value >= new DateTime('$previous_date')"] = i::__('A data inicial deve ser maior que a data final da fase anterior');
                }
            }

            if (($next = $this->nextPhase) && !$this->isLastPhase) {
                if($next->isLastPhase) {
                    $next_date = $next->publishTimestamp;
                    $next_error = i::__('A data final deve ser menor que a data de publicação do resultado final');
                } else if(is_object($next)){
                    $next_date = $next->registrationFrom;
                    $next_error = i::__('A data final deve ser menor que a data inicial da próxima fase');
                }

                if ($next_date) {
                    $next_date = $next_date->format('Y-m-d H:i:s');
                    $validations['registrationTo']["\$value <= new DateTime('$next_date')"] = $next_error;
                }
            }
        });

        /**
         * Validação das datas da fase de coleta de dados em relação às fases anterior e posterior
         */
        $app->hook('entity(EvaluationMethodConfiguration).validations', function(&$validations) {
            $previous_phase = $this->previousPhase;
            if ($previous_phase instanceof Opportunity) {
                if ($date_from = $previous_phase->registrationFrom) {
                    $date_from = $date_from->format('Y-m-d H:i:s');
                    $validations['evaluationFrom']["\$value >= new DateTime('$date_from')"] = i::__('A data inicial deve ser maior que a data de inicio da coleta de dados da fase anterior');
                }
                if ($date_to = $previous_phase->registrationTo) {
                    $date_to = $date_to->format('Y-m-d H:i:s');
                    $validations['evaluationTo']["\$value >= new DateTime('$date_to')"] = i::__('A data final não pode ser menor que a data final da fase anterior');
                }

            } else if ($previous_phase instanceof EvaluationMethodConfiguration) {
                if ($date_from = $previous_phase->evaluationTo) {
                    $date_from = $date_from->format('Y-m-d H:i:s');
                    $validations['evaluationFrom']["\$value >= new DateTime('$date_from')"] = i::__('A data inicial deve ser maior que a data de término das avaliações da fase anterior');
                }
            }

            if (!$this->id) {
                $next_phase = $this->opportunity->lastPhase;
                $error_message = i::__('A data final deve ser menor que a data de final de publicação dos resultados');
            } else {
                $next_phase = $this->nextPhase;
                $error_message = i::__('A data final deve ser menor que a data de inicio da próxima fase');
            }

            $date_to = null;

            if($next_phase instanceof Opportunity) {
                $date_to = $next_phase->isLastPhase ? $next_phase->publishTimestamp :  $next_phase->registrationFrom;
            } else if(is_object($next_phase)) {
                $date_to = $next_phase->evaluationFrom;
            }

            if($date_to) {
                $date_to = $date_to->format('Y-m-d H:i:s');
                $validations['evaluationTo']["\$value < new DateTime('$date_to')"] = $error_message;
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
                if (!$opportunity->isDataCollection) {
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
            'private'=> true,
            'default'=> true,
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
                "siteName" => $app->view->dict("site: name", false),
                "user" => $registration->owner->name,
                "baseUrl" => $app->getBaseUrl(),
                "opportunityTitle" => $opportunity->name
            ];
            $email_params = [
                "from" => $app->config["mailer.from"],
                "to" => ($registration->owner->emailPrivado ??
                         $registration->owner->emailPublico ??
                         $registration->ownerUser->email),
                "subject" => sprintf(i::__("Aviso sobre a sua inscrição na " .
                                           "oportunidade %s"),
                                     $opportunity->name),
                "body" => $app->renderMustacheTemplate($template, $params)
            ];
            if (!isset($email_params["to"])) {
                return;
            }
            $app->createAndSendMailMessage($email_params);
        }
        return;
    }
}
