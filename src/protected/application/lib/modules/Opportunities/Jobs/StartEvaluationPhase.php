<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;

class StartEvaluationPhase extends JobType
{
    const SLUG = "StartEvaluationPhase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "StartEvaluationPhase:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        $app = App::i();
        
        /** @var Opportunity $opportunity */
        $opportunity = $job->opportunity;
        
        /** @var \OpportunityPhases\Module $module */
        $module = $app->modules['OpportunityPhases'];
        
        /**
         * importa as inscrições da fase anterior 
         */
        if (!$opportunity->isFirstPhase && !$opportunity->isDataCollection ) {
            $module->importLastPhaseRegistrations($opportunity->previousPhase, $opportunity, false);
        }

        echo "> $job->opportunity " . __CLASS__ . "\n\n";
        return true;
    }
}