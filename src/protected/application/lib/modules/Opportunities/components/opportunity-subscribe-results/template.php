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
    v1-embed-tool
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
                <label class="phase-stepper__type--name"><?= i::__('Tipo') ?></label>: <label class="phase-stepper__type--item">{{evaluationMethods[item.type].name}}</label>
            </p>
        </div>
    </template>
    <template #header-actions="{step, item}">     
        <div class="phase-actions">
            <modal title="<?= i::esc_attr__('Configurações de suporte')?>" classes="modalEmbedTools" v-if="item.__objectType == 'opportunity' && !item.isLastPhase">
                <template #default="modal">
                    <v1-embed-tool route="supportbuilder" :id="item.id"></v1-embed-tool>
                </template>
                <template #button="modal">
                    <a class="support" @click="modal.open"><?= i::__('Suporte') ?> <mc-icon name="external"></mc-icon></a>
                </template>
            </modal>   

            <a class="expand-stepper" v-if="step.active" @click="step.close()"><label><?= i::__('Fechar') ?></label><mc-icon name="arrowPoint-up"></mc-icon></a>
            <a class="expand-stepper" v-if="!step.active" @click="step.open()"><label><?= i::__('Expandir') ?></label> <mc-icon name="arrowPoint-down"></mc-icon></a>
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