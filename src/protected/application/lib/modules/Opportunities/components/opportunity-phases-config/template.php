<?php
/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-stepper-vertical
    opportunity-create-evaluation-phase
    opportunity-create-data-collect-phase
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <h2 v-if="index">{{item.name}}</h2>
        <h2 v-if="!index"><?= i::__('Período de inscrição') ?></h2>
        <p v-if="item.__objectType == 'opportunity'">
            <?= i::__('Tipo') ?>: <?= i::__('Coleta de dados') ?>
        </p>
        <p v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <?= i::__('Tipo') ?>: {{item.type.name}}
        </p>
    </template>

    <template #default="{index, item}">
        <div v-if="index > 0">
            <entity-field :entity="item" prop="name" hide-required></entity-field>
            <div class="grid-12">
                <div class="col-12">
                    <opportunity-create-evaluation-phase :entity="item"></opportunity-create-evaluation-phase>
                </div>
                <div class="col-12">
                    <opportunity-create-data-collect-phase :entity="item"></opportunity-create-data-collect-phase>
                </div>
            </div>
        </div>
        <template v-if="item.__objectType == 'opportunity'">

        </template>

        <template v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <entity-field :entity="item" prop="evaluationFrom" hide-required></entity-field>
            <entity-field :entity="item" prop="evaluationTo" hide-required></entity-field>
        </template>
    </template>
</mc-stepper-vertical>