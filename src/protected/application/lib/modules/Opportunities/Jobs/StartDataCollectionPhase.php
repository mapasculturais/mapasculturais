<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;

class StartDataCollectionPhase extends JobType
{
    const SLUG = "StartDataCollectionPhase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "StartDataCollectionPhase:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        echo "> $job->opportunity " . __CLASS__ . "\n\n";
        return true;
    }
}