<?php

/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    mc-link
    confirm-button
    mc-stepper-vertical
    mc-link
    opportunity-create-evaluation-phase
    opportunity-create-data-collect-phase
    opportunity-phase-config-data-collection
    opportunity-phase-config-results
    opportunity-phase-config-evaluation
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <div class="phase-stepper">
            <h2 v-if="index" class="phase-stepper__name">{{item.name}}</h2>
            <h2 v-if="!index" class="phase-stepper__period"><?= i::__('Período de inscrição') ?></h2>
            <p class="phase-stepper__type" v-if="item.__objectType == 'opportunity' && !item.isLastPhase">
                <label class="phase-stepper__type--name"><?= i::__('Tipo') ?></label>:
                <label class="phase-stepper__type--item"><?= i::__('Coleta de dados') ?></label>
            </p>
            <p v-if="item.__objectType == 'evaluationmethodconfiguration'" class="phase-stepper__type">
                <label class="phase-stepper__type--name"><?= i::__('Tipo') ?></label>: <label class="phase-stepper__type--item">{{item.type.name}}</label>
            </p>
        </div>
    </template>
    <template #default="{index, item}">
        <!-- fase de coleta de dados -->
        <template v-if="item.__objectType == 'opportunity' && !item.isLastPhase">
            <opportunity-phase-config-data-collection :phases="phases" :phase="item"></opportunity-phase-config-data-collection>
        </template>

        <!-- fase de avaliação -->
        <template v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <opportunity-phase-config-evaluation :phases="phases" :phase="item"></opportunity-phase-config-evaluation>
        </template>

        <!-- fase de publicação de resultado -->
        <template v-if="item.isLastPhase">
            <opportunity-phase-config-results :phases="phases" :phase="item"></opportunity-phase-config-results>
        </template>
    </template>
    <template #after-li="{index, item}">
        <div v-if="index == phases.length-2" class="add-phase grid-12">
            <div class="add-phase__evaluation col-12">
                <opportunity-create-evaluation-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]" @create="addInPhases"></opportunity-create-evaluation-phase>
            </div>
            <p><label class="add-phase__collection"><?= i::__("ou") ?></label></p>
            <div class="add-phase__collection col-12">
                <opportunity-create-data-collect-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]" @create="addInPhases"></opportunity-create-data-collect-phase>
            </div>
        </div>
    </template>
</mc-stepper-vertical>