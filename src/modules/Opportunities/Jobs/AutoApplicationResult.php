<?php

namespace Opportunities\Jobs;

use MapasCulturais\App;
use MapasCulturais\Entities\RegistrationEvaluation;
use MapasCulturais\Definitions\JobType;
use MapasCulturais\Entities\Registration;

class AutoApplicationResult extends JobType
{
    const SLUG = "AutoApplicationResult";

    protected function _generateId(array $data, string $start_string, string $interval_string, int $iterations)
    {
        return "AutoApplicationResult:{$data['registration']->id}";
    }

    protected function _execute(\MapasCulturais\Entities\Job $job)
    {
        $app = App::i();

        $opportunity = $job->opportunity;
        $registration = $job->registration;

        $evaluation_type = $opportunity->evaluationMethodConfiguration->type->id;

        if ($registration->needsTiebreaker() && !$registration->evaluationMethod->getTiebreakerEvaluation($registration)) {
            return;
        }
        $conn = $app->em->getConnection();
        $evaluations = $conn->fetchAll(
            "
                SELECT
                   *
                FROM
                    evaluations
                WHERE
                    registration_id = {$registration->id}"
        );

        $all_status_sent = true;

        foreach ($evaluations as $evaluation) {
            $registration_evaluation = $evaluation['evaluation_id'] ? $app->repo('RegistrationEvaluation')->find($evaluation['evaluation_id']) : false;

            if (!$registration_evaluation && $evaluation['evaluation_status'] !== RegistrationEvaluation::STATUS_SENT) {
                $all_status_sent = false;
            }
        }

        if ($all_status_sent) {
            if ($evaluation_type == 'continuous') {
                $value = $job->newStatus ?? $registration->consolidatedResult;
            }

            if ($evaluation_type == 'simple'){
                $value = $registration->consolidatedResult;
            }

            if ($evaluation_type == 'documentary') {
                $value = $registration->consolidatedResult == 1 ? Registration::STATUS_APPROVED : Registration::STATUS_NOTAPPROVED;
            }

            if ($evaluation_type == 'qualification') {
                $value = $registration->consolidatedResult == 'Habilitado' ? Registration::STATUS_APPROVED : Registration::STATUS_NOTAPPROVED;
            }

            $app->disableAccessControl();
            $registration->setStatus($value);
            $registration->save();
            $app->enableAccessControl();
        }
        
        return true;
    }
}
