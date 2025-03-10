<?php
namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Definitions\JobType;

class RefreshViewEvaluations extends JobType
{
    const SLUG = "RefreshViewEvaluations";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "RefreshViewEvaluations";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job){

        $app = App::i(); 
        $conn = $app->em->getConnection();

        if($app->config['app.log.jobs']) {
            $app->log->info('JOB: REFRESH MATERIALIZED VIEW evaluations');
        }

        $conn->executeQuery('REFRESH MATERIALIZED VIEW evaluations;');
        
        return true;
    }
}