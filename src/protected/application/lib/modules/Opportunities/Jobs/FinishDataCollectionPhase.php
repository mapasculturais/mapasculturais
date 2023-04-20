<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;

class FinishDataCollectionPhase extends JobType
{
    const SLUG = "FinishDataCollectionPhase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "FinishDataCollectionPhase:{$data['opportunity']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        $app = App::i();
        
        /** @var Opportunity $opportunity */
        $opportunity = $job->opportunity;
        
        /** @var Opportunity $next_phase */
        $next_phase = $opportunity->nextPhase;
        
        /** @var \OpportunityPhases\Module $module */
        $module = $app->modules['OpportunityPhases'];
        
        /**
         * envia as inscrições para a fase de publicação dos resultados
         */
        if ($next_phase && $next_phase->isLastPhase && $opportunity->isDataCollection && !$opportunity->evaluationMethodConfiguration) {
            $module->importLastPhaseRegistrations($opportunity, $next_phase);
        }

        echo "> $job->opportunity " . __CLASS__ . "\n\n";
        return true;
    }    
}