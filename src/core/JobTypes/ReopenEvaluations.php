<?php
namespace MapasCulturais\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\RegistrationEvaluation;

/**
 * Tipo de job para reabertura de avaliações
 * 
 * Este job type é responsável por reabrir avaliações de inscrições
 * que foram fechadas, permitindo que avaliadores revisem suas avaliações.
 * 
 * @package MapasCulturais\JobTypes
 */
class ReopenEvaluations extends JobType
{
    /**
     * @const string SLUG Identificador único do tipo de job
     */
    const SLUG = "reopenEvaluations";

    /**
     * Gera um ID único para o job
     * 
     * @param array $data Dados do job
     * @param string $start_string String de início
     * @param string $interval_string String de intervalo
     * @param int $iterations Número de iterações
     * @return string ID único do job
     */
    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "reopenEvaluation:{$data['agentRelation']}".uniqid();
    }

    /**
     * Executa o job de reabertura de avaliações
     * 
     * @param \MapasCulturais\Entities\Job $job Instância do job
     * @return bool False para indicar que o job não se repete
     */
    protected function _execute(\MapasCulturais\Entities\Job $job){
        $app = App::i();

        $agent_relation = $job->agentRelation;
        $opportunity = $agent_relation->owner->opportunity;
        $user = $agent_relation->agent->user;

        $query = $app->em->createQuery('
            SELECT e.id 
            FROM MapasCulturais\\Entities\\RegistrationEvaluation e 
            JOIN e.registration r
            WHERE e.user =:user AND r.opportunity =:opportunity AND e.status = 2'
        );

        $query->setParameters([
            'user' => $user,
            'opportunity' => $opportunity
        ]);
        $evaluation_ids = $query->getScalarResult();
        foreach ($evaluation_ids as $id){
            $id = $id['id'];
            $evaluation = $app->repo('RegistrationEvaluation')->find($id);
            $evaluation->status = RegistrationEvaluation::STATUS_EVALUATED;
            $evaluation->save(true, true);
            $app->em->clear();
            $app->log->info("Rebrindo avaliação - ".$evaluation);
        }

        return false;
    }
    
}