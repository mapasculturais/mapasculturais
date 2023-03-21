<?php
namespace MapasCulturais\JobTypes;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\RegistrationEvaluation;

class ReopenEvaluations extends JobType
{
    const SLUG = "reopenEvaluations";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "reopenEvaluation:{$data['agentRelation']}".uniqid();
    }

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