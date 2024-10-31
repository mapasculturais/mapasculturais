<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
    opportunity-phase-publish-date-config
    opportunity-enable-claim
    opportunity-category
    opportunity-ranges-config
    opportunity-proponent-types
');
?>
    <div class="opportunity-data-collection grid-12">
        <?php $this->applyTemplateHook('opportunity-data-collection-config','before')?>
        <div class="grid-12 col-12 opportunity-data-collection__section">
            <?php $this->applyTemplateHook('opportunity-data-collection-config','begin')?>
            <entity-field v-if="!phase.isFirstPhase" :entity="phase" prop="name" :autosave="3000" classes="col-12 sm:col-12"></entity-field>
            <entity-field :entity="phase" prop="registrationFrom" :autosave="3000" :min="fromDateMin?._date" :max="fromDateMax?._date" classes="col-6 sm:col-12"></entity-field>
            <entity-field v-if="!firstPhase?.isContinuousFlow" :entity="phase" prop="registrationTo" :autosave="3000" :min="toDateMin?._date" :max="toDateMax?._date" classes="col-6 sm:col-12"></entity-field>

            <?php $this->applyTemplateHook('opportunity-data-collection-config','end')?>
        </div>
        <div class="opportunity-data-collection__limits col-12" v-if="phase.isFirstPhase">
                <div class="opportunity-data-collection__fields">
                    <entity-field :entity="phase" prop="vacancies" :min="0" :autosave="3000" class="field__limits">
                        <template #info>
                            <?php $this->info('editais-oportunidades -> configuracoes -> total-vagas') ?>
                        </template>
                    </entity-field>
                    <entity-field :entity="phase" prop="totalResource" :min="0" :autosave="3000" class="field__limits">
                        <template #info>
                            <?php $this->info('editais-oportunidades -> configuracoes -> valor-total') ?>
                        </template>
                    </entity-field>
                    <entity-field :entity="phase" prop="registrationLimit" :min="0" :autosave="3000" class="field__limits">
                        <template #info>
                            <?php $this->info('editais-oportunidades -> configuracoes -> limite-inscricoes') ?>
                        </template>
                    </entity-field>
                    <entity-field :entity="phase" prop="registrationLimitPerOwner" :min="0" :autosave="3000" class="field__limits">
                        <template #info>
                            <?php $this->info('editais-oportunidades -> configuracoes -> limite-inscritos-por-agente') ?>
                        </template>
                    </entity-field>
                </div>
            <?php $this->applyTemplateHook('opportunity-data-collection-config','end')?>
        </div>

        <div class="col-12">
            <opportunity-category v-if="phase.isFirstPhase" :entity="phase"></opportunity-category>
        </div>

        <div class="col-12" v-if="phase.isFirstPhase">
            <opportunity-proponent-types :entity="phase"></opportunity-proponent-types>
        </div>

        <?php $this->applyTemplateHook('opportunity-data-collection-config','after')?>

        <div class="col-12" v-if="phase.isFirstPhase">
            <opportunity-ranges-config :entity="phase"></opportunity-ranges-config>
        </div>

        <div class="col-12 sm:col-12">
            <?php $this->applyComponentHook('bottom') ?>
        </div>

         <div class="col-12 grid-12 opportunity-data-collection__config-button">
            <mc-link :entity="phase" route='formBuilder' class="config-phase__info-button button--primary button col-6" icon="external" right-icon>
            <?= i::__("Configurar formulário") ?>
            </mc-link>
        </div>

        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration' && !firstPhase?.isContinuousFlow">
            <div class="opportunity-data-collection__horizontal-line col-12 "></div>
            <opportunity-phase-publish-date-config  :phase="phase" :phases="phases" hide-description hide-button></opportunity-phase-publish-date-config>
        </template>

        <div class="opportunity-data-collection__delete col-12" v-if="!phase.isLastPhase && !phase.isFirstPhase">
            <mc-confirm-button message="<?=i::esc_attr__('Confirma a execução da ação?')?>" @confirm="deletePhase($event, phase, index)">
                <template #button="modal">
                    <button :class="['phase-delete__trash button button--text button--sm', {'disabled' : !phase.currentUserPermissions.remove}]" @click="modal.open()">
                        <div class="icon">
                            <mc-icon name="trash" class="secondary__color"></mc-icon> 
                        </div>
                        <h5>{{ text('excluir_fase_coleta_dados') }}</h5>
                    </button>
                </template>
            </mc-confirm-button>
        </div>
    </div>