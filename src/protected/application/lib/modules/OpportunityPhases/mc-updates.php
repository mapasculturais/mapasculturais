<?php
use MapasCulturais\i;

return [
    'criando fases de resultado final para as oportunidades existentes sem fase final' => function () {
        DB_UPDATE::enqueue('Opportunity', 'parent_id IS NULL', function(MapasCulturais\Entities\Opportunity $opportunity) {
            $phases = $opportunity->allPhases;

            $last_created_phase = array_pop($phases);

            if(!$last_created_phase->isLastPhase) {
                $end_date = $last_created_phase->evaluationMethodConfiguration ? 
                    $last_created_phase->evaluationMethodConfiguration->evaluationTo :
                    $last_created_phase->registrationTo;

                $class = get_class($opportunity);

                /** @var Opportunity $last_phase */
                $last_phase = new $class;
                $last_phase->owner = $opportunity->owner->refreshed();
                $last_phase->status = -1;
                $last_phase->parent = $opportunity->refreshed();
                $last_phase->name = i::__('Publicação final do resultado');
                $last_phase->type = $opportunity->type;
                $last_phase->isLastPhase = true;
                $last_phase->isOpportunityPhase = true;
                $last_phase->isDataCollection = '0';
                $last_phase->publishTimestamp = $end_date;
                $last_phase->publishedRegistrations = $last_created_phase->publishedRegistrations;
                $last_phase->save(true);

                $last_phase->enqueueRegistrationSync();
            }
        });
    }
];