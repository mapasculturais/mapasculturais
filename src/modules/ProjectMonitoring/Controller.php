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

        $collection_phase = new ($parent_phase::class)();
        $collection_phase->isDataCollection = true;
        $collection_phase->isReportingPhase = true;
        $collection_phase->isFinalReportingPhase = $params['collectionPhase']['isFinalReportingPhase'] ?? false;
        $collection_phase->name = $params['collectionPhase']['name'] ?? '';
        $collection_phase->registrationFrom = $params['collectionPhase']['registrationFrom']['_date'] ?? false;
        $collection_phase->registrationTo = $params['collectionPhase']['registrationTo']['_date'] ?? false;
        $collection_phase->setStatus(-1);
        
        $collection_phase->parent = $parent_phase;
        $collection_phase->type = $parent_phase->type;
        $collection_phase->registrationCategories = $parent_phase->registrationCategories;
        $collection_phase->registrationRanges = $parent_phase->registrationRanges;
        $collection_phase->registrationProponentTypes = $parent_phase->registrationProponentTypes;
        $collection_phase->ownerEntity = $parent_phase->ownerEntity;
        $collection_phase->owner = $parent_phase->owner;

        $evaluation_phase = new Entities\EvaluationMethodConfiguration();
        $evaluation_phase->opportunity = $collection_phase;
        $evaluation_phase->type = 'documentary';
        $evaluation_phase->name = $params['evaluationPhase']['name'] ?? '';
        $evaluation_phase->evaluationFrom = $params['evaluationPhase']['evaluationFrom']['_date'] ?? false;
        $evaluation_phase->evaluationTo = $params['evaluationPhase']['evaluationTo']['_date'] ?? false;

        $collection_errors = $collection_phase->getValidationErrors();
        $evaluation_errors = $evaluation_phase->getValidationErrors();

        if (empty($collection_errors) && empty($evaluation_errors)) {
            $collection_phase->save();
            $evaluation_phase->save();

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