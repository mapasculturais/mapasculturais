<?php

namespace ProjectMonitoring;

use \MapasCulturais\App;
use \MapasCulturais\Entities;

class Controller extends \MapasCulturais\Controller {
    
    public function POST_reportingPhase() {
        $this->requireAuthentication();
        $params = $this->data;

        $app = App::i();

        /** @var Entities\Opportunity */
        $parent_phase = $app->repo('opportunity')->find($params['parent']);

        if(isset($params['collectionPhase']['registrationFrom']['_date'])) {
            $collective_phase_registration_from = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($params['collectionPhase']['registrationFrom']['_date'], 0, 19), new \DateTimeZone('UTC'));
            $collective_phase_registration_from->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        }

        if(isset($params['collectionPhase']['registrationTo']['_date'])) {
            $collective_phase_registration_to = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($params['collectionPhase']['registrationTo']['_date'], 0, 19), new \DateTimeZone('UTC'));
            $collective_phase_registration_to->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        }

        $collection_phase = new ($parent_phase::class)();
        $collection_phase->ownerEntity = $parent_phase->ownerEntity;

        $collection_phase->isDataCollection = true;
        $collection_phase->isReportingPhase = true;
        $collection_phase->isFinalReportingPhase = $params['collectionPhase']['isFinalReportingPhase'] ?? false;
        $collection_phase->name = $params['collectionPhase']['name'] ?? '';
        $collection_phase->registrationFrom = isset($params['collectionPhase']['registrationFrom']['_date']) ? $collective_phase_registration_from : false;
        $collection_phase->registrationTo = isset($params['collectionPhase']['registrationTo']['_date']) ? $collective_phase_registration_to : false;
        $collection_phase->setStatus(-1);
        
        $collection_phase->parent = $parent_phase;
        $collection_phase->type = $parent_phase->type;
        $collection_phase->registrationCategories = $parent_phase->registrationCategories;
        $collection_phase->registrationRanges = $parent_phase->registrationRanges;
        $collection_phase->registrationProponentTypes = $parent_phase->registrationProponentTypes;
        $collection_phase->owner = $parent_phase->owner;

        if(isset($params['evaluationPhase']['evaluationFrom']['_date'])) {
            $evaluation_phase_evaluation_from = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($params['evaluationPhase']['evaluationFrom']['_date'], 0, 19), new \DateTimeZone('UTC'));
            $evaluation_phase_evaluation_from->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        }

        if(isset($params['evaluationPhase']['evaluationTo']['_date'])) {
            $evaluation_phase_evaluation_to = \DateTime::createFromFormat('Y-m-d\TH:i:s', substr($params['evaluationPhase']['evaluationTo']['_date'], 0, 19), new \DateTimeZone('UTC'));
            $evaluation_phase_evaluation_to->setTimezone(new \DateTimeZone(date_default_timezone_get()));
        }

        $evaluation_phase = new Entities\EvaluationMethodConfiguration();
        $evaluation_phase->opportunity = $collection_phase;
        $evaluation_phase->type = 'continuous';
        $evaluation_phase->name = $params['evaluationPhase']['name'] ?? '';
        $evaluation_phase->evaluationFrom = isset($params['evaluationPhase']['evaluationFrom']['_date']) ? $evaluation_phase_evaluation_from : false;
        $evaluation_phase->evaluationTo = isset($params['evaluationPhase']['evaluationTo']['_date']) ? $evaluation_phase_evaluation_to : false;

        $collection_errors = $collection_phase->getValidationErrors();
        $evaluation_errors = $evaluation_phase->getValidationErrors();

        if (empty($collection_errors) && empty($evaluation_errors)) {
            $collection_phase->save(true);
            $evaluation_phase->save(true);

            $collection_phase->evaluationMethodConfiguration = $evaluation_phase;
            $this->json([
                'collectionPhase' => $collection_phase,
                'evaluationPhase' => $evaluation_phase,
            ]);
        } else {
            $this->json([
                'errors' => true,
                'collectionErrors' => $collection_errors,
                'evaluationErrors' => $evaluation_errors,
            ], 400);
        }
    }
}