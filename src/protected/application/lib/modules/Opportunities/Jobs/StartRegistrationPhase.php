<?php
namespace Opportunities\Jobs;

use MapasCulturais\Definitions\JobType;

class StartRegistrationPhase extends JobType
{
    const SLUG = "startregistrationphase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "startregistrationphase:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        echo "Job Executado\n\n";
        return true;
    }
    
}