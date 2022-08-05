<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;

class StartEvaluationPhase extends JobType
{
    const SLUG = "startevaluationphase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "startevaluationphase:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){

    }
    
}