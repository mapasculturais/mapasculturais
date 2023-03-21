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
            <h2 v-if="!index" class="phase-stepper__period"><?= i::__("Inscritos em fase 1") ?></h2>
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