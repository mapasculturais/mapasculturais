<?php
/**
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 * @var MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->import('
    mc-link
    mc-modal
    mc-stepper-vertical
    opportunity-phase-status
    opportunity-phase-list-evaluation
    v1-embed-tool
');
?>
<mc-stepper-vertical :items="phases" allow-multiple>
    <template #header-title="{index, item}">        
        <div class="stepper-header__content">
            <div class="info">
                <h3 v-if="item.isFirstPhase" class="info__title"><?= i::__('Período de inscrição') ?></h3>
                <h3 v-if="!item.isFirstPhase && !item.isLastPhase" class="info__title"><?= sprintf(i::__('Inscritos em %s'), '{{item.name}}') ?></h3>
                <h3 v-if="item.isLastPhase" class="info__title">{{item.name}}</h3>
                <div v-if="!item.isLastPhase" class="info__type">
                    <span class="title"> <?= i::__('Tipo') ?>: </span>
                    <span v-if="item.__objectType == 'opportunity'" class="type"><?= i::__('Coleta de dados') ?></span>
                    <span v-if="item.__objectType == 'evaluationmethodconfiguration'" class="type">{{item.type.name}}</span>
                </div>

            </div>
            <div class="dates">
                <div v-if="!item.isLastPhase" class="date">
                    <div class="date__title"> <?= i::__('Data de início') ?> </div>
                    <div v-if="item.registrationFrom" class="date__content">{{item.registrationFrom.date('2-digit year')}} {{item.registrationFrom.time('numeric')}}</div>
                    <div v-if="item.evaluationFrom" class="date__content">{{item.evaluationFrom.date('2-digit year')}} {{item.evaluationFrom.time('numeric')}}</div>
                </div>
                <div v-if="!item.isLastPhase && (!phases[0].isContinuousFlow || (phases[0].isContinuousFlow && phases[0].hasEndDate))" class="date">
                    <div class="date__title"> <?= i::__('Data final') ?> </div>
                    <div v-if="item.registrationTo" class="date__content">{{item.registrationTo.date('2-digit year')}} {{item.registrationTo.time('numeric')}}</div>
                    <div v-if="item.evaluationTo" class="date__content">{{item.evaluationTo.date('2-digit year')}} {{item.evaluationTo.time('numeric')}}</div>
                </div>
                <div v-if="showPublishTimestamp(item)" class="date">
                    <div class="date__title"> <?= i::__('Data de publicação') ?> </div>
                    <div class="date__content">{{publishTimestamp(item)?.date('2-digit year')}}</div>
                </div>
            </div>
        </div>
    </template>
    <template #header-actions="{step, item}">     
        <div class="stepper-header__actions">
            <mc-modal title="<?= i::esc_attr__('Configurações de suporte')?>" classes="modalEmbedTools" v-if="item.__objectType == 'opportunity' && !item.isLastPhase">
                <template #default="modal">
                    <!-- <v1-embed-tool route="supportbuilder" :id="item.id"></v1-embed-tool> -->
                </template>
                <template #button="modal">
                    <mc-link class="button button--icon" route="suporte/configuracao" :params="[item.id]" icon="external" right-icon> <?= i::__('Suporte') ?> </mc-link>
                </template>
            </mc-modal>   
            <a class="expand-stepper" v-if="step.active" @click="step.close()"><label><?= i::__('Diminuir') ?></label><mc-icon name="arrowPoint-up"></mc-icon></a>
            <a class="expand-stepper" v-if="!step.active" @click="step.open()"><label><?= i::__('Expandir') ?></label> <mc-icon name="arrowPoint-down"></mc-icon></a>
        </div>
    </template>
    <template #default="{index, item}">
        
        <template v-if="item.__objectType == 'evaluationmethodconfiguration'">
            <opportunity-phase-list-evaluation :entity="item" :phases="phases"></opportunity-phase-list-evaluation>
        </template>

        <template v-if="item.__objectType == 'opportunity'">
            <opportunity-phase-status :entity="item"  :phases="phases" :tab="tab"></opportunity-phase-status>
        </template>
    </template>
</mc-stepper-vertical>