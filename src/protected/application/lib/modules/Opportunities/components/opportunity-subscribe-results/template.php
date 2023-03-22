<?php

/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    mc-stepper-vertical
    opportunity-phase-list-data-collection
    opportunity-phase-list-evaluation
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

        <template v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <opportunity-phase-list-evaluation :entity="item" :phases="phases"></opportunity-phase-list-evaluation>
        </template>

        <template v-if="item.__objectType == 'opportunity'">
            <opportunity-phase-list-data-collection :entity="item" :phases="phases"></opportunity-phase-list-data-collection>
        </template>

    </template>
</mc-stepper-vertical>