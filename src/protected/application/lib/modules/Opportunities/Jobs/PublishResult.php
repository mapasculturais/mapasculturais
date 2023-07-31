<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Opportunity;

class PublishResult extends JobType
{
    const SLUG = "PublishResult";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "PublishResult:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        /** @var Opportunity $opportunity */
        
        $opportunity = $job->opportunity;
        $opportunity->publishRegistrations();
        
        return true;
    }
}