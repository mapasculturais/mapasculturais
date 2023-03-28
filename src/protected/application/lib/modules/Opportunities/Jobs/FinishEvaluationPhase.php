<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;

class FinishEvaluationPhase extends JobType
{
    const SLUG = "FinishEvaluationPhase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "FinishEvaluationPhase:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        echo "> $job->opportunity " . __CLASS__ . "\n\n";
        return true;
    }
}