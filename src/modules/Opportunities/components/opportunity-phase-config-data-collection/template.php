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
');
?>
    <div class="opportunity-data-collection grid-12">
        <div class="grid-12 col-12 opportunity-data-collection__section">
            <entity-field v-if="!phase.isFirstPhase" :entity="phase" prop="name" :autosave="3000" classes="col-12 sm:col-12"></entity-field>
            <entity-field :entity="phase" prop="registrationFrom" :autosave="3000" :min="fromDateMin?._date" :max="fromDateMax?._date" classes="col-6 sm:col-12"></entity-field>
            <entity-field :entity="phase" prop="registrationTo" :autosave="3000" :min="toDateMin?._date" :max="toDateMax?._date" classes="col-6 sm:col-12"></entity-field>

            <div class="col-12 grid-12">
                <mc-link :entity="phase" route='formBuilder' class="config-phase__info-button button--primary button col-6" icon="external" right-icon>
                    <?= i::__("Configurar formulário") ?>
                </mc-link>
            </div>

            <div class="opportunity-data-collection__category col-12">
                <opportunity-category v-if="phase.isFirstPhase" :entity="phase"></opportunity-category>
                
                <div class="opportunity-data-collection__registration ">
                    <h4 class="bold col-12"><?= i::__("Limites na inscrição") ?></h4>
                    <div class="opportunity-data-collection__fields grid-12">
                        <h5 class="bold col-12 "><?= i::__("Total de vagas")?></h5>
                        <entity-field :entity="phase" prop="registrationLimit" label="<?=i::esc_attr__('Defina o número limite de vagas  para o edital ou oportunidade')?>"  :autosave="3000" classes="opportunity-data-collection__field col-12"></entity-field>
                        <h5 class="bold col-12 "><?= i::__("Inscrições por agente")?></h5>
                        <entity-field :entity="phase" prop="registrationLimitPerOwner" label="<?=i::esc_attr__('Defina o número de inscrições máximas para um agente (pessoa ou coletivo)')?>" :autosave="3000" classes="opportunity-data-collection__field col-12"></entity-field>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-12 sm:col-12">
            <?php $this->applyComponentHook('bottom') ?>
        </div>
        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'">
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