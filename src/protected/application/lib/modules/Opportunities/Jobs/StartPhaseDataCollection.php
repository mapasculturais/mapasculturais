<?php
namespace Opportunities\Jobs;

use MapasCulturais\Definitions\JobType;

class StartPhaseDataCollection extends JobType
{
    const SLUG = "StartPhaseDataCollection";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "StartPhaseDataCollection:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){

    }
    
}