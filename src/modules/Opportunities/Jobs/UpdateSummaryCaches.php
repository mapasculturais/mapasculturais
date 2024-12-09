<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Entities\Opportunity;
use MapasCulturais\Entities\EvaluationMethodConfiguration;
use MapasCulturais\Definitions\JobType;

class UpdateSummaryCaches extends JobType
{
    const SLUG = "UpdateSummaryCaches";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        $opportunity = $data['opportunity'] ?? (object) ['id' => null];
        $emc = $data['evaluationMethodConfiguration'] ?? (object) ['id' => null];

        return "UpdateSummaryCaches:{$opportunity->id}:{$emc->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){
        $app = App::i();
        
        /** @var Opportunity $opportunity */
        if($opportunity = $job->opportunity){
            $app->mscache->delete($opportunity->summaryCacheKey);
            $opportunity->getSummary(true);
        }

        /** @var EvaluationMethodConfiguration $evaluationMethodConfiguration */
        if($evaluationMethodConfiguration = $job->evaluationMethodConfiguration){
            $app->mscache->delete($evaluationMethodConfiguration->summaryCacheKey);
            $evaluationMethodConfiguration->getSummary(true);
        }
       
        return true;
    }
}