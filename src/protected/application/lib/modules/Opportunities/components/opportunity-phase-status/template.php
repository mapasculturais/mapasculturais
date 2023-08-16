<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    opportunity-phase-publish-config-registration
    opportunity-phase-list-registrations
')
?>
<mc-card>
    <div :class="[{'grid-12': tab!='registrations'}, {'grid-12':entity.isLastPhase}, 'opportunity-phase-status']">
        <div v-if="entity.summary?.registrations && !entity.isLastPhase" class="col-12 grid-12">
            <opportunity-phase-list-registrations :entity="entity" :tab="tab"></opportunity-phase-list-registrations>
            <opportunity-phase-publish-config-registration v-if="entity.isFirstPhase" :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox ></opportunity-phase-publish-config-registration>

        </div>
        <div :class="[{'col-12':  tab!='registrations' && entity.isFirstPhase}, 'opportunity-phase-status_action--center col-12']">
            <opportunity-phase-publish-config-registration v-if="entity.isLastPhase" :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox ></opportunity-phase-publish-config-registration>
            
        </div>

        <div v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'" class="col-12">
            <div v-if="!entity.isLastPhase" class="opportunity-phase-status__line col-12"></div>
            <opportunity-phase-publish-config-registration v-if="!entity.isLastPhase && !entity.isFirstPhase" :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox></opportunity-phase-publish-config-registration>
        </div>
    </div>
</mc-card>