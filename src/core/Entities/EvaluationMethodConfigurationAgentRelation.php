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
    const STATUS_ACTIVE = 1;

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
        parent::__construct();
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

        $this->owner->opportunity->enqueueToPCacheRecreation([$this->agent->user]);
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
     * Atualiza o resumo de avaliações do avaliador.
     *
     * Este método é chamado nas seguintes situações:
     * 1) Sempre que uma avaliação é iniciada entity(RegistrationEvaluation)
     * 2) Sempre que o status de uma avaliação é alterado entity(RegistrationEvaluation).setStatus(<<*>>)
     * 3) Sempre que uma avaliação é removida entity(RegistrationEvaluation).remove:after
     * 4) Sempre que uma inscrição for atribuída para o usuário avaliar entity(recreatePermissionCache:after)
     *
     * @param bool $flush  Indica se deve salvar as alterações no banco de dados.
     * @param bool $pending  Indica se deve atualizar o resumo das avaliações pendentes.
     * @param bool $started  Indica se deve atualizar o resumo das avaliações iniciadas.
     * @param bool $completed  Indica se deve atualizar o resumo das avaliações concluídas.
     * @param bool $sent  Indica se deve atualizar o resumo das avaliações enviadas.
     * @return void
     * @throws PermissionDenied
     * @throws WorkflowRequest
     */
    function updateSummary(bool $flush = false, bool $pending = true, bool $started = true, bool $completed = true, bool $sent = true): void
    {
        $entity = $this->owner;
        $app = App::i();
        /** @var \MapasCulturais\Connection $conn */
        $conn = $app->em->getConnection();

        $user = $this->agent->user;
        $data = $this->metadata["summary"] ?? [];

        /**
         * Constrói a query para contar as avaliações com base no status.
         *
         * @param int|null $status Status da avaliação (0 = iniciada, 1 = concluída, 2 = enviada).
         * @return int Retorna a contagem de avaliações.
         */
        $buildQuery = function ($status = null) use ($user, $entity, $conn): int {
            $statusCondition = is_null($status) ? "e.status IS NULL" : "e.status = {$status} AND e.registration_id IN (SELECT r.id FROM registration r WHERE r.opportunity_id = {$entity->opportunity->id})";

            $query = "
                SELECT DISTINCT count(e.registration_id)
                FROM registration_evaluation e
                WHERE {$statusCondition} AND user_id = {$user->id}
            ";

            return $conn->fetchScalar($query);
        };

        // Atualiza as avaliações pendentes
        if ($pending) {
            $query = "
                SELECT DISTINCT count(e.registration_id)
                FROM evaluations e
                WHERE opportunity_id = {$entity->opportunity->id} AND e.evaluation_status IS NULL AND valuer_user_id = {$user->id}
            ";
            $data['pending'] = $conn->fetchScalar($query);
        } else {
            $data['pending'] = $data['pending'] ?? 0;
        }

        // Atualiza as avaliações iniciadas
        if ($started) {
            $data['started'] = $buildQuery(0);
        } else {
            $data['started'] = $data['started'] ?? 0;
        }

        // Atualiza as avaliações concluídas
        if ($completed) {
            $data['completed'] = $buildQuery(1);
        } else {
            $data['completed'] = $data['completed'] ?? 0;
        }

        // Atualiza as avaliações enviadas
        if ($sent) {
            $data['sent'] = $buildQuery(2);
        } else {
            $data['sent'] = $data['sent'] ?? 0;
        }

        if(is_object($this->metadata)) {
            $metadata = $this->metadata;
        } else {
            $metadata = (object) [];
        }

        $metadata->summary = $data;

        $conn->update('agent_relation', ['metadata' => json_encode($metadata)], ['id' => $this->id]);
    }

    protected function canUserRemove($user): bool
    {
        return $this->owner->canUser('manageEvaluationCommittee', $user);
    }
}
