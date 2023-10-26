<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Definitions\JobType;

class FinishEvaluationPhase extends JobType
{
    const SLUG = "FinishEvaluationPhase";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "FinishEvaluationPhase:{$data['opportunity']->id}";
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
        if ($next_phase && $next_phase->isLastPhase) {
            $next_phase->syncRegistrations();
        }

        $opportunity->enqueueToPCacheRecreation();

        return true;
    }
}