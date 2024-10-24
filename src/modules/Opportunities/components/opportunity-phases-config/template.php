<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-stepper-vertical
    opportunity-create-data-collect-phase
    opportunity-create-evaluation-phase
    opportunity-phase-config-data-collection
    opportunity-phase-config-evaluation
    opportunity-phase-config-results
');
?>
<mc-stepper-vertical class="opportunity-phases-config" :items="phases" allow-multiple>
    <template #header-title="{index, item}">
        <div class="stepper-header__content opportunity-phases-config__content">
            <div class="info opportunity-phases-config__info">
                <h3 v-if="index" class="info__title">{{item.name}}</h3>
                <h3 v-if="!index" class="info__title"><?= i::__('Período de inscrição') ?></h3>
                <div v-if="!item.isLastPhase" class="info__type">
                    <span class="title"> 
                        <?= i::__('Tipo') ?>: 
                        <span v-if="item.__objectType == 'opportunity' && !item.isLastPhase" class="type"><?= i::__('Coleta de dados') ?></span>
                        <span v-if="item.__objectType == 'evaluationmethodconfiguration'" class="type">{{evaluationTypes[item.type.id]}}</span>
                    </span>
                    <span v-if="item.__objectType == 'evaluationmethodconfiguration' && evaluationTypes[item.type.id] == 'Avaliação Técnica'"> <?php $this->info('editais-oportunidades -> avaliacao-tecnica -> avaliacao-tecnica') ?> </span>
                </div>
            </div>

            <div class="dates opportunity-phases-config__dates">
                <div v-if="!item.isLastPhase" class="date">
                    <div class="date__title"> <?= i::__('Data de início') ?> </div>
                    <div v-if="item.registrationFrom" class="date__content">{{item.registrationFrom.date('2-digit year')}} {{item.registrationFrom.time('numeric')}}</div>
                    <div v-if="item.evaluationFrom" class="date__content">{{item.evaluationFrom.date('2-digit year')}} {{item.evaluationFrom.time('numeric')}}</div>
                </div>
                <div v-if="!item.isLastPhase && (!firstPhase?.isContinuousFlow || firstPhase?.hasEndDate)" class="date">
                    <div class="date__title"> <?= i::__('Data final') ?> </div>
                    <div v-if="item.registrationTo" class="date__content">{{item.registrationTo.date('2-digit year')}} {{item.registrationTo.time('numeric')}}</div>
                    <div v-if="item.evaluationTo" class="date__content">{{item.evaluationTo.date('2-digit year')}} {{item.evaluationTo.time('numeric')}}</div>
                </div>
                <div v-if="showPublishTimestamp(item) && (!firstPhase?.isContinuousFlow || (firstPhase?.isContinuousFlow && firstPhase?.hasEndDate))" class="date">
                    <div class="date__title"> <?= i::__('Data publicação') ?> </div>
                    <div class="date__content">{{publishTimestamp(item)?.date('2-digit year')}}</div>
                </div>
            </div>
        </div>
    </template>
    <template #default="{index, item}">
        <!-- fase de coleta de dados -->
        <template v-if="item.__objectType == 'opportunity' && !item.isLastPhase">
            <mc-card><opportunity-phase-config-data-collection :phases="phases" :phase="item"></opportunity-phase-config-data-collection></mc-card>
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
        <template v-if="index == phases.length-2">
            <div v-if="showButtons() && entity.registrationFrom && entity.registrationTo && !(firstPhase?.isContinuousFlow && firstPhase?.hasEndDate && !lastPhase.publishTimestamp)" class="add-phase grid-12">
                <div class="add-phase__evaluation col-12">
                    <opportunity-create-evaluation-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]" @create="addInPhases"></opportunity-create-evaluation-phase>
                </div>
                <p><label class="add-phase__collection"><?= i::__("ou") ?></label></p>
                <div class="add-phase__collection col-12">
                    <opportunity-create-data-collect-phase :opportunity="entity" :previousPhase="item" :lastPhase="phases[index+1]" @create="addInPhases"></opportunity-create-data-collect-phase>
                </div>
            </div>
            
            <mc-alert v-if="!entity.registrationFrom && !entity.registrationTo" type="warning">
                <p><small class="required"><?= i::__("A data e hora da 'Coleta de dados' precisa estar preenchida para adicionar novas fases.") ?></small></p>
            </mc-alert>

            <mc-alert v-if="firstPhase?.isContinuousFlow && firstPhase?.hasEndDate && !lastPhase?.publishTimestamp" type="warning">
                <p><small class="required"><?= i::__("A data e hora da 'Publicação final' precisa estar preenchida para adicionar novas fases.") ?></small></p>
            </mc-alert>
                
            <div v-if="!showButtons()" class="info-message helper">
                <mc-icon name="exclamation"></mc-icon>
                <?= i::__('Não se pode criar novas fases após a publicação do resultado final') ?>
            </div>
        </template>
    </template>
</mc-stepper-vertical>