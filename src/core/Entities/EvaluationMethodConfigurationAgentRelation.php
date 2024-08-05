<?php
namespace MapasCulturais\Entities;

use MapasCulturais\App;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\Exceptions\PermissionDenied;
use MapasCulturais\Exceptions\WorkflowRequest;
use MapasCulturais\GuestUser;
use MapasCulturais\JobTypes\ReopenEvaluations;

/**
 * Relação que define um avaliador de uma oportunidade
 * 
 * @property \MapasCulturais\Entities\EvaluationMethodConfiguration $owner
 * @property \MapasCulturais\Entities\Agent $agent
 * 
 * @ORM\Entity
 * @ORM\entity(repositoryClass="MapasCulturais\Repository")
 */
class EvaluationMethodConfigurationAgentRelation extends AgentRelation {
    const STATUS_SENT = 10;
    const STATUS_DISABLED = 8;

    /**
     * @var \MapasCulturais\Entities\EvaluationMethodConfiguration
     *
     * @ORM\ManyToOne(targetEntity="MapasCulturais\Entities\EvaluationMethodConfiguration")
     * @ORM\JoinColumns({
     *   @ORM\JoinColumn(name="object_id", referencedColumnName="id", onDelete="CASCADE")
     * })
     */
    protected $owner;


    public function __construct()
    {
        $this->metadata =  [
            "summary" => [
                "pending" => 0, 
                "started" => 0, 
                "completed" => 0,
                "sent" => 0
            ]
        ];
    }
    
    
    function delete($flush = false) {
        $app = App::i();
        
        $this->checkPermission('remove');

        $evaluations = \MapasCulturais\App::i()->repo('RegistrationEvaluation')->findByOpportunityAndUser($this->owner->opportunity, $this->agent->user);
        $app->disableAccessControl();    
        foreach($evaluations as $eval){
            $eval->delete($flush);
        }
        $app->enableAccessControl();

        $this->owner->opportunity->enqueueToPCacheRecreation();
        parent::delete($flush);
    }
    
    function reopen($flush = true){
        $this->owner->checkPermission('manageEvaluationCommittee');

        $app = App::i();

        $app->applyHookBoundTo($this,"{$this->hookPrefix}.reopen:before");
        $this->status = self::STATUS_ENABLED;

        $this->save($flush);

        $job = $app->enqueueJob(ReopenEvaluations::SLUG, ['agentRelation' => $this]);
        $app->applyHookBoundTo($this,"{$this->hookPrefix}.reopen:after", [$job]);
    }

    function disable($flush = true){
        $this->owner->checkPermission('manageEvaluationCommittee');

        $app = App::i();

        $app->applyHookBoundTo($this,"{$this->hookPrefix}.disable:before");

        $app->disableAccessControl();
        $this->status = self::STATUS_DISABLED;
        $this->save($flush);
        $app->enableAccessControl();

        $app->applyHookBoundTo($this,"{$this->hookPrefix}.disable:after");
    }

    function enable($flush = true){
        $this->owner->checkPermission('manageEvaluationCommittee');

        $app = App::i();

        $app->applyHookBoundTo($this,"{$this->hookPrefix}.enable:before");

        $app->disableAccessControl();
        $this->status = self::STATUS_ENABLED;
        $this->save($flush);
        $app->enableAccessControl();
        
        $app->applyHookBoundTo($this,"{$this->hookPrefix}.enable:after");
    }


    /**
     * Atualiza o resumo de avaliações do avaliador
     * Este método é chamado nas seguintes situações
     * 1) Sempre que uma avaliação é iniciada entity(RegistrationEvaluation)
     * 2) Sempre que o status de uma avaliação é alterado entity(RegistrationEvaluation).setStatus(<<*>>)
     * 3) Sempre que uma avaliação é removida entity(RegistrationEvaluation).remove:after
     * 4) Sempre que uma inscrição for atribuida para o usuario avaliar entity(recreatePermissionCache:after) Verificando a permissão evaluateOnTime e o usuario for o mesmo da relação
     * @param bool $flush 
     * @return void 
     * @throws PermissionDenied 
     * @throws WorkflowRequest 
     */
    function updateSummary(bool $flush = false, $pending = true, $started = true, $completed =  true, $sent = true): void
    {
        $entity = $this->owner;
        $app = App::i();
        $conn = $app->em->getConnection();

        $user = $this->agent->user;
        $data = $this->metadata["summary"] ?? [];

        $_buildQuery = function($status = null) use ($user, $entity, $conn) {

            
            if(is_null($status)) {
                $complement = " e.status IS NULL ";
            } else {
                $complement = " e.status = {$status} AND e.registration_id IN (SELECT r.id FROM registration r WHERE r.opportunity_id ={$entity->opportunity->id})";
            }


            $query = "SELECT DISTINCT count(e.registration_id) as qtd
            FROM registration_evaluation e 
            WHERE 
                {$complement} AND 
                user_id = {$user->id}";


// eval(\psy\sh());
            return  $conn->fetchAssoc($query);
        };

        $buildQuery = function($colluns = "*", $params = "", $type = "fetchAll") use ($conn, $entity){
            return $conn->$type("SELECT {$colluns} FROM evaluations e WHERE opportunity_id = {$entity->opportunity->id} {$params}");
        };

        // Retorna as pendentes
        if($pending = $_buildQuery()) {

            $data['pending'] = $pending['qtd'];
        }

        // Retorna as iniciadas
        if($started = $_buildQuery('0')) {
            $data['started'] = $started['qtd'];
        }

        // Retorna as concluidas
        if ($completed = $_buildQuery(1)) {
            $data['completed'] = $completed['qtd'];
        }

         // Retorna as enviadas
         if ($sent = $_buildQuery(2)) {
            $data['sent'] = $sent['qtd'];
        }

        $this->metadata = ['summary' => $data];

        $app->disableAccessControl();
        $this->save($flush);
        $app->enableAccessControl();
    }

    protected function canUserRemove($user): bool
    {
        return $this->owner->canUser('manageEvaluationCommittee', $user);
    }
}
