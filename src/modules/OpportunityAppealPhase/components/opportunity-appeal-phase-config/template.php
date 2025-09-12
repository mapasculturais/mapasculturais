<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-accordion
    mc-confirm-button
    mc-icon
    mc-link
    mc-loading
    opportunity-committee-groups
    opportunity-phase-config-status
    opportunity-phase-list-evaluation
    opportunity-phase-publish-date-config
    opportunity-phase-status
');
?>

<div class="opportunity-appeal-phase-config col-12">
    <h4 v-if="tab === 'config'" class="opportunity-appeal-phase-config__title bold"><?= i::__("Etapa suplementar") ?></h4>
    <div v-if="!entity && tab === 'config'" class="opportunity-appeal-phase-config__button">
        <button v-if="!processing" class="button button--primary" @click="createAppealPhase()">
            <?= i::__("Adicionar recurso") ?>
        </button>

        <div v-if="processing" class="col-12">
            <mc-loading :condition="processing"> <?= i::__('carregando') ?></mc-loading>
        </div>
    </div>
    <div v-if="entity" class="opportunity-appeal-phase-config__appeals">
        <div v-if="tab === 'config'" class="opportunity-appeal-phase-config__delete col-12">
            <mc-confirm-button @confirm="deleteAppealPhase()">
                <template #message="message">
                    <p>
                        <?= i::__('Você tem certeza que deseja excluir essa fase de recurso?') ?>
                    </p>
                    <br><br>
                    <p>
                        <mc-alert type="warning">
                            <strong><?= i::__('ATENÇÃO') ?>: </strong> <?= i::__('TODOS os recursos enviados, avaliados ou não, serão <strong>excluídos permanentemente</strong>. Esta ação não poderá ser desfeita.') ?>
                        </mc-alert>
                    </p>
                </template> 
                <template #button="modal">
                    <button :class="['phase-delete__trash button button--text button--sm', {'disabled' : !entity.currentUserPermissions.remove}]" @click="modal.open()">
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
            <template #content v-if="tab === 'config'">
                <div class="opportunity-appeal-phase-config__content-title">
                    <h3 class="bold"><?= i::__('Configuração da fase') ?></h3>
                    <div class="opportunity-appeal-phase-config__datepicker">
                        <entity-field :entity="entity" prop="registrationFrom" field-type="date" label="<?= i::__('Início') ?>" :autosave="3000" :min="fromDateMin?._date" :max="fromDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                        <entity-field v-if="!firstPhase?.isContinuousFlow" field-type="date" label="<?= i::__('Término') ?>" :entity="entity" prop="registrationTo" :autosave="3000" :min="toDateMin?._date" :max="toDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                    </div>
                </div>
                <div class="opportunity-appeal-phase-config__config-button">
                    <mc-link :entity="entity" route="formBuilder" class="button button--icon button--primary button--md"> 
                        <?= i::__('Configurar formulário') ?> 
                        <mc-icon name="external" size="sm"></mc-icon>
                    </mc-link>
                </div>
            </template>

            <template #content v-if="tab === 'registrations'">
                <opportunity-phase-status :entity="phase.appealPhase" :phases="phases" :tab="tab"></opportunity-phase-status>
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

            <template #content v-if="tab === 'config'">
                <div class="opportunity-appeal-phase-config__content-title">
                    <h3 class="bold"><?= i::__('Configuração da fase') ?></h3>
                    <div class="opportunity-appeal-phase-config__datepicker">
                        <entity-field :entity="entity.evaluationMethodConfiguration" prop="evaluationFrom" label="<?= i::__('Início') ?>" field-type="date" :autosave="3000" :min="fromDateMin?._date" :max="fromDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                        <entity-field v-if="!firstPhase?.isContinuousFlow" field-type="date" :entity="entity.evaluationMethodConfiguration" prop="evaluationTo" label="<?= i::__('Término') ?>" :autosave="3000" :min="toDateMin?._date" :max="toDateMax?._date" classes="col-6 sm:col-12"></entity-field>
                    </div>
                </div>
                <div class="opportunity-appeal-phase-config__checkboxes field">
                    <entity-field class="input-box" :entity="entity" hide-required  :editable="true" prop="allow_proponent_response" :autosave="3000"></entity-field>
                </div> 

                <mc-alert v-if="entity.allow_proponent_response" type="warning" class="entity-owner-pending">
                    <div>
                        <?= i::__('Ao habilitar esta função, o detalhamento das avaliações ficará disponível para consulta mesmo antes da divulgação oficial do resultado da fase') ?></strong>
                    </div>
                </mc-alert>

                <div class="opportunity-appeal-phase-config__config-button opportunity-appeal-phase-config__add-evaluation-committee">
                    <opportunity-committee-groups :entity="entity.evaluationMethodConfiguration"></opportunity-committee-groups>
                </div>

                <opportunity-phase-config-status :phase="entity"></opportunity-phase-config-status>

                <opportunity-phase-publish-date-config :phase="entity" :phases="phases" hide-description hide-button></opportunity-phase-publish-date-config>

                <div v-if="entity.evaluationMethodConfiguration.evaluateSelfApplication">
                    <entity-field :entity="entity.evaluationMethodConfiguration" type="checkbox" prop="autoApplicationAllowed" label="<?php i::esc_attr_e('Autoaplicação de resultados')?>" :autosave="300" classes="col-12 sm:col-12"></entity-field>
                </div>
            </template>

            <template #content v-if="tab === 'registrations'">
                <opportunity-phase-list-evaluation :entity="phase.appealPhase.evaluationMethodConfiguration" :phases="phases" :tab="tab"></opportunity-phase-list-evaluation>
            </template>
        </mc-accordion>
    </div>

</div>