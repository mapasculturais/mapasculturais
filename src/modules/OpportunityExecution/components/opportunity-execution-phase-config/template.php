<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-accordion
    mc-alert
    mc-confirm-button
    mc-icon
    mc-loading
    opportunity-committee-groups
    opportunity-phase-config-status
    opportunity-phase-publish-date-config
');
?>

<div class="opportunity-execution-phase-config col-12">
    <h4 v-if="tab === 'config'" class="opportunity-execution-phase-config__title bold"><?= i::__("Fase de Execução") ?></h4>

    <div v-if="!entity && tab === 'config'" class="opportunity-execution-phase-config__button">
        <button v-if="!processing" class="button button--primary" @click="createExecutionPhase()">
            <?= i::__("Adicionar fase de execução") ?>
        </button>
        <div v-if="processing" class="col-12">
            <mc-loading :condition="processing"><?= i::__('carregando') ?></mc-loading>
        </div>
    </div>

    <div v-if="entity" class="opportunity-execution-phase-config__phase">
        <div v-if="tab === 'config'" class="opportunity-execution-phase-config__delete col-12">
            <mc-confirm-button @confirm="deleteExecutionPhase()">
                <template #message>
                    <p><?= i::__('Você tem certeza que deseja excluir a fase de execução?') ?></p>
                    <br>
                    <p>
                        <mc-alert type="warning">
                            <strong><?= i::__('ATENÇÃO') ?>: </strong>
                            <?= i::__('Todos os pedidos enviados, avaliados ou não, serão <strong>excluídos permanentemente</strong>. Esta ação não poderá ser desfeita.') ?>
                        </mc-alert>
                    </p>
                </template>
                <template #button="modal">
                    <button :class="['button button--text button--sm', {'disabled': !entity.currentUserPermissions.remove}]" @click="modal.open()">
                        <div class="icon">
                            <mc-icon name="trash" class="secondary__color"></mc-icon>
                        </div>
                        <h5 class="bold"><?= i::__('Excluir fase de execução') ?></h5>
                    </button>
                </template>
            </mc-confirm-button>
        </div>

        <mc-accordion :withText="true">
            <template #title>
                <div class="opportunity-execution-phase-config__title">
                    <h3 class="bold"><?= i::__('Pedidos de alteração') ?></h3>
                    <div class="info__type">
                        <span class="title">
                            <?= i::__('Tipo') ?>:
                            <span class="type"><?= i::__('Fase de Execução') ?></span>
                        </span>
                    </div>
                </div>
                <div class="dates opportunity-execution-phase-config__dates">
                    <div class="date">
                        <h6 class="date__title"><?= i::__('Data de início') ?></h6>
                        <h4 class="date__content">{{ entity?.registrationFrom?.date('numeric year') }} {{ entity?.registrationFrom?.time('numeric') }}</h4>
                    </div>
                    <div class="date">
                        <h6 class="date__title"><?= i::__('Data final') ?></h6>
                        <h4 class="date__content">{{ entity?.registrationTo?.date('numeric year') }} {{ entity?.registrationTo?.time('numeric') }}</h4>
                    </div>
                </div>
            </template>
            <template #icon></template>

            <template #content v-if="tab === 'config'">
                <div class="opportunity-execution-phase-config__content-title">
                    <h3 class="bold"><?= i::__('Configuração da fase') ?></h3>
                    <div class="opportunity-execution-phase-config__datepicker">
                        <entity-field :entity="entity" prop="registrationFrom" label="<?= i::__('Início') ?>" :autosave="3000" classes="col-6 sm:col-12"></entity-field>
                        <entity-field :entity="entity" prop="registrationTo"   label="<?= i::__('Término') ?>" :autosave="3000" classes="col-6 sm:col-12"></entity-field>
                    </div>

                    <div class="col-12 opportunity-execution-phase-config__categories">
                        <h4 class="bold"><?= i::__('Categorias de pedido') ?></h4>
                        <p class="description"><?= i::__('Defina os tipos de pedido que o agente poderá abrir. O agente escolhe a categoria ao enviar cada pedido.') ?></p>
                        <entity-field :entity="entity" prop="registrationCategories" type="textarea" label="<?= i::__('Categorias (uma por linha)') ?>" :autosave="3000" classes="col-12"></entity-field>
                    </div>
                </div>

                <div class="opportunity-execution-phase-config__evaluation col-12">
                    <h4 class="bold"><?= i::__('Comissão avaliadora') ?></h4>
                    <p class="description"><?= i::__('Configure quem avaliará os pedidos de alteração enviados pelos agentes.') ?></p>
                    <opportunity-committee-groups v-if="entity.evaluationMethodConfiguration" :entity="entity.evaluationMethodConfiguration"></opportunity-committee-groups>
                </div>

                <opportunity-phase-config-status :phase="entity"></opportunity-phase-config-status>
                <opportunity-phase-publish-date-config :phase="entity" :phases="phases" hide-description hide-button></opportunity-phase-publish-date-config>
            </template>

            <template #content v-if="tab === 'registrations'">
                <opportunity-phase-status :entity="entity" :phases="phases" :tab="tab"></opportunity-phase-status>
            </template>
        </mc-accordion>
    </div>
</div>
