<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\EvaluationMethodConfiguration;

class RedistributeCommitteeRegistrations extends JobType
{
    const SLUG = "RedistribRegs";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "RedistribRegs:{$data['evaluationMethodConfiguration']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        /** @var EvaluationMethodConfiguration $emc */
        $emc = $job->evaluationMethodConfiguration;

        if (!$emc) {
            return true;
        }
        
        $emc->redistributeCommitteeRegistrations();
        
        return true;
    }
}