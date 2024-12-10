<?php

namespace OpportunityAppealPhase;

use MapasCulturais\App;
use MapasCulturais\i;
use MapasCulturais\Controllers;

class Module extends \MapasCulturais\Module {
    public function _init() {
        $app = App::i();

        /* Endpoint de criação de fase de recurso */
        $app->hook('POST(opportunity.createAppealPhase)', function() use ($app) {
            /** @var Controllers\Opportunity $this  */

            $opportunity = $this->requestedEntity;

            $opportunity->checkPermission('@control');

            $class_name = $opportunity->getSpecializedClassName();

            $appeal_phase = new $class_name();
            $appeal_phase->parent = $opportunity;
            $appeal_phase->status = -1;
            $appeal_phase->name = sprintf(i::__('Fase de recurso para %s'), $opportunity->name);

            $appeal_phase->ownerEntity = $opportunity->ownerEntity;
            $appeal_phase->registrationCategories = $opportunity->registrationCategories;
            $appeal_phase->registrationRanges = $opportunity->registrationRanges;
            $appeal_phase->registrationProponentTypes = $opportunity->registrationProponentTypes;

            $appeal_phase->save(true);

            $this->json($appeal_phase);
        });
    }

    public function register()
    {
    }
}