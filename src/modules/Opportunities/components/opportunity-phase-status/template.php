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
            <opportunity-phase-list-registrations :entity="entity" :phases="phases" :tab="tab"></opportunity-phase-list-registrations>
        </div>
        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'">
            <div v-if="entity.isLastPhase" :class="['opportunity-phase-status_action--center col-12']">
                <opportunity-phase-publish-config-registration  :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox ></opportunity-phase-publish-config-registration>
            </div>
            <div v-if="!entity.isLastPhase" class="col-12">
                <div class="opportunity-phase-status__line col-12"></div>
                <opportunity-phase-publish-config-registration  :phase="entity" :phases="phases" :tab="tab" hide-datepicker hide-checkbox></opportunity-phase-publish-config-registration>
            </div>
        </template>
    </div>
</mc-card>