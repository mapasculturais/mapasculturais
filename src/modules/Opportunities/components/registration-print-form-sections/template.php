<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    registration-field-view
');
?>
<div class="registration-print-form-sections" data-registration-print-ready>
    <template v-for="phase in dataCollectionPhases" :key="phase.id">
        <div class="registration-print-form-sections__block">
            <h2 v-if="phase.opportunity?.isFirstPhase"><?= i::__('Inscrição') ?></h2>
            <h2 v-else-if="phase.opportunity?.name">{{ phase.opportunity.name }}</h2>
            <registration-field-view
                :registration="registration"
                :phase-id="+phase.id"
            ></registration-field-view>
        </div>
    </template>
</div>
