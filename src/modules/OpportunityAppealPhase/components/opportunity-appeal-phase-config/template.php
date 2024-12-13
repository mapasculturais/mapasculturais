<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    opportunity-committee-groups
    opportunity-phase-publish-date-config
    mc-confirm-button
    mc-loading
    mc-accordion
    entity-field
    mc-icon
');
?>

<div class="opportunity-appeal-phase-config col-12">
    <h4 class="opportunity-appeal-phase-config__title bold"><?= i::__("Etapa suplementar") ?></h4>
    <div v-if="!entity" class="opportunity-appeal-phase-config__button">
        <button v-if="!processing" class="button button--primary" @click="createAppealPhase()">
            <?= i::__("Adicionar recurso") ?>
        </button>

        <div v-if="processing" class="col-12">
            <mc-loading :condition="processing"> <?= i::__('Carregando') ?></mc-loading>
        </div>
    </div>
    <div v-if="entity" class="opportunity-appeal-phase-config__appeals">
        <div class="opportunity-appeal-phase-config__delete col-12">
            <mc-confirm-button message="<?=i::esc_attr__('Confirma a execução da ação?')?>">
                <template #button="modal">
                    <button :class="['phase-delete__trash button button--text button--sm', {'disabled' : !phase.currentUserPermissions.remove}]" @click="modal.open()">
                        <div class="icon">
                            <mc-icon name="trash" class="secondary__color"></mc-icon> 
                        </div>
                        <h5 class="bold"><?= i::__('Excluir recurso') ?></h5>
                    </button>
                </template>
            </mc-confirm-button>
        </div>  
        <mc-accordion :withText="true">
            <template #title>
                <div class="opportunity-appeal-phase-config__title">
                    <h3 class="bold"><?= i::__('Recurso') ?></h3>
                    <div class="info__type">
                        <span class="title">
                            <?= i::__('Tipo') ?>:
                            <span class="type"><?= i::__('Coleta de dados') ?></span>
                        </span>
                    </div>
                </div>
                <div class="dates opportunity-appeal-phase-config__dates">
                    <div class="date">
                        <h6 class="date__title"> <?= i::__('Data de início') ?> </h6>
                        <h4 class="date__content">{{ registrationFrom }}</h4>
                    </div>
                    <div v-if="(!firstPhase?.isContinuousFlow || firstPhase?.hasEndDate)" class="date">
                        <h6 class="date__title"> <?= i::__('Data final') ?> </h6>
                        <h4 class="date__content">{{ registrationTo }}</h4>
                    </div>
                </div>
            </template>
            <template #icon>
            </template>
            <template #content>
                <div class="opportunity-appeal-phase-config__content-title">
                    <h3 class="bold"><?= i::__('Configuração da fase') ?></h3>
                    <div class="opportunity-appeal-phase-config__datepicker">
                        <entity-field :entity="entity" prop="registrationFrom" field-type="date" label="<?= i::__('Início') ?>" :autosave="3000" :min="fromDateMin?._date" :max="fromDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                        <entity-field v-if="!firstPhase?.isContinuousFlow" field-type="date" label="<?= i::__('Término') ?>" :entity="entity" prop="registrationTo" :autosave="3000" :min="toDateMin?._date" :max="toDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                    </div>
                </div>
                <div class="opportunity-appeal-phase-config__config-button">
                    <button class="button button--icon button--primary button--md"> 
                        <?= i::__('Configurar formulário') ?> 
                        <mc-icon name="external" size="sm"></mc-icon>
                    </button>
                </div>
            </template>
        </mc-accordion>

        <mc-accordion :withText="true">
            <template #title>
                <div class="opportunity-appeal-phase-config__title">
                    <h3 class="bold"><?= i::__('Resposta do recurso') ?></h3>
                    <div class="info__type">
                        <span class="title">
                            <?= i::__('Tipo') ?>:
                            <span class="type"><?= i::__('Avaliação contínua') ?></span>
                        </span>
                    </div>
                </div>
                <div class="dates opportunity-appeal-phase-config__dates">
                    <div class="date">
                        <h6 class="date__title"> <?= i::__('Data de início') ?> </h6>
                        <h4 class="date__content">{{ evaluationFrom }}</h4>
                    </div>
                    <div v-if="(!firstPhase?.isContinuousFlow || firstPhase?.hasEndDate)" class="date">
                        <h6 class="date__title"> <?= i::__('Data final') ?> </h6>
                        <h4 class="date__content">{{ evaluationTo }}</h4>
                    </div>
                </div>
            </template>
            <template #icon>
            </template>
            <template #content>
                <div class="opportunity-appeal-phase-config__content-title">
                    <h3 class="bold"><?= i::__('Configuração da fase') ?></h3>
                    <div class="opportunity-appeal-phase-config__datepicker">
                        <entity-field :entity="entity.evaluationMethodConfiguration" prop="evaluationFrom" label="<?= i::__('Início') ?>" field-type="date" :autosave="3000" :min="fromDateMin?._date" :max="fromDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                        <entity-field v-if="!firstPhase?.isContinuousFlow" field-type="date" :entity="entity.evaluationMethodConfiguration" prop="evaluationTo" label="<?= i::__('Término') ?>" :autosave="3000" :min="toDateMin?._date" :max="toDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                    </div>
                </div>
                <div class="opportunity-appeal-phase-config__checkboxes field">
                    <input type="checkbox" id="more-response" v-model="moreResponse">
                    <label for="more-response"><?= i::__('Possibilitar mais de uma resposta do proponente') ?></label> 
                </div> 
                <div class="opportunity-appeal-phase-config__config-button opportunity-appeal-phase-config__add-evaluation-committee">
                    <button v-if="showButtonEvaluationCommittee" class="button button--icon button--primary button--md" @click="addEvaluationCommittee()"> 
                        <mc-icon name="add"></mc-icon>
                        <?= i::__('Adicionar pessoa avaliadora') ?> 
                    </button>
                    <opportunity-committee-groups v-if="!showButtonEvaluationCommittee" :entity="entity.evaluationMethodConfiguration"></opportunity-committee-groups>
                </div>
                <opportunity-phase-publish-date-config :phase="entity" :phases="phases" hide-description></opportunity-phase-publish-date-config>
            </template>
        </mc-accordion>
    </div>

</div>