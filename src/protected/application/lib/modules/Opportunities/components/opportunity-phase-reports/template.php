<?php

/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    mc-stepper-vertical
    v1-embed-tool
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <div class="phase-stepper">
            <h2 v-if="index" class="phase-stepper__name">{{item.name}}</h2>
            <h2 v-if="!index" class="phase-stepper__period"><?= i::__("Fase 1") ?></h2>
        </div>
    </template>
    <template #default="{index, item}">

        <template v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <mapas-card>
                <v1-embed-tool route="opportunityreport" :id="entity.id"></v1-embed-tool>
                <!-- entity.opportunity.id esta undefined -->
            </mapas-card>
        </template>

        <template v-if="item.__objectType == 'opportunity'">
            <mapas-card>
                <v1-embed-tool route="opportunityreport" :id="entity.id"></v1-embed-tool>
            </mapas-card>
        </template>

    </template>
</mc-stepper-vertical>