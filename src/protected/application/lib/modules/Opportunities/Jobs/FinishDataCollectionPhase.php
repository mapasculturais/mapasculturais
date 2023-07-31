<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
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
        
        /**
         * envia as inscrições para a fase de publicação dos resultados
         */
        if ($next_phase && $next_phase->isLastPhase && $opportunity->isDataCollection && !$opportunity->evaluationMethodConfiguration) {
            $next_phase->syncRegistrations();
        }

        $opportunity->enqueueToPCacheRecreation();

        return true;
    }    
}