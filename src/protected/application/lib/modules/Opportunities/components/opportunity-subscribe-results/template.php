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
        <div class="stepper-header__content">
            <div class="info">
                <h2 v-if="index" class="info__title">{{item.name}}</h2>
                <h2 v-if="!index" class="info__title"><?= i::__('Período de inscrição') ?></h2>
                <div class="info__type">
                    <span class="title"> <?= i::__('Tipo') ?>: </span>
                    <span v-if="item.__objectType == 'opportunity' && !item.isLastPhase" class="type"><?= i::__('Coleta de dados') ?></span>
                    <span v-if="item.__objectType == 'evaluationmethodconfiguration'" class="type">{{evaluationMethods[item.type].name}}</span>
                </div>
            </div>
        </div>
    </template>
    <template #header-actions="{step, item}">     
        <div class="stepper-header__actions">
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