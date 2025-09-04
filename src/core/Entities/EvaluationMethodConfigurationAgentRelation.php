<?php
namespace MapasCulturais\Entities;

use MapasCulturais\App;
use Doctrine\ORM\Mapping as ORM;
use MapasCulturais\EvaluationMethod;
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
    
    function save($flush = false)
    {
        $this->owner->__skipQueuingPCacheRecreation = true;
        $this->owner->opportunity->__skipQueuingPCacheRecreation = true;
        parent::save($flush);
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

        $this->owner->opportunity->__skipQueuingPCacheRecreation = true;
        $this->owner->__skipQueuingPCacheRecreation = true;

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
    function updateSummary(): void
    {
        $app = App::i();
        
        if ($app->config['app.log.summary']) {
            $app->log->debug("SUMMARY: Atualizando o resumo de avaliações do avaliador {$this->agent->name}");
        }

        if(is_object($this->metadata)) {
            $metadata = $this->metadata;
        } else {
            $metadata = (object) [];
        }

        $user = $this->agent->user;
        $evaluation_method_configuration = $this->owner;
        $metadata->summary = $evaluation_method_configuration->getValuerSummary($user, $this->group);

        $conn = $app->em->getConnection();
        $conn->update('agent_relation', ['metadata' => json_encode($metadata)], ['id' => $this->id]);
    }

    protected function canUserRemove($user): bool
    {
        return $this->owner->canUser('manageEvaluationCommittee', $user);
    }

    protected function canUserModify($user): bool
    {
        return $this->owner->canUser('manageEvaluationCommittee', $user);
    }
}
