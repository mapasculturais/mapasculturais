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
            <h2 class="phase-stepper__name">{{ isJoinedPhaseLabel(index) }}</h2>
        </div>
    </template>
    <template #default="{index, item}">

        <template v-if="isJoinedPhase(index)">
            <mapas-card v-if="item.opportunity && item.opportunity.id">
                <v1-embed-tool route="opportunityreport" :id="item.opportunity.id"></v1-embed-tool>
            </mapas-card>
            <mapas-card v-if="item && item.id">
                <v1-embed-tool route="opportunityreport" :id="item.id"></v1-embed-tool>
            </mapas-card>
        </template>

        <template v-else>
            <template v-if="item.__objectType == 'evaluationmethodconfiguration'">
                <mapas-card v-if="item.opportunity && item.opportunity.id">
                    <v1-embed-tool route="opportunityreport" :id="item.opportunity.id"></v1-embed-tool>
                </mapas-card>
            </template>

            <template v-if="item.__objectType == 'opportunity'">
                <mapas-card v-if="item && item.id">
                    <v1-embed-tool route="opportunityreport" :id="item.id"></v1-embed-tool>
                </mapas-card>
            </template>
        </template>

    </template>
</mc-stepper-vertical>