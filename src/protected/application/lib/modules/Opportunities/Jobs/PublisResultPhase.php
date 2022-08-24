<?php
namespace Opportunities\Jobs;

use MapasCulturais\Definitions\JobType;

class PublisResultPhase extends JobType
{
    const SLUG = "PublisResultPhase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "PublisResultPhase:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){

    }
    
}