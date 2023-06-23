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
        <div class="opportunity-data-collection__horizontal-line col-12 "></div>
        <entity-field :entity="phase" prop="registrationFrom" :autosave="300" :min="minDate?._date" :max="phase.registrationTo?._date" classes="col-6 sm:col-12"></entity-field>
        <entity-field :entity="phase" prop="registrationTo" :autosave="300" :min="phase.registrationFrom?._date" :max="maxDate?._date" classes="col-6 sm:col-12"></entity-field>
        <div class="col-12 grid-12">
            <mc-link :entity="phase" route='formBuilder' class="config-phase__info-button button--primary button col-6" icon="external" right-icon>
              <?= i::__("Configurar formulário") ?>
            </mc-link>
        </div>
        <div class="opportunity-data-collection__category col-12">
            <div v-if="phase.isFirstPhase">
                <opportunity-category :entity="phase"></opportunity-category>
            </div>
            <!-- <div class="opportunity-data-collection__vertical-line"></div> -->
            <div class="opportunity-data-collection__registration">
                <entity-field :entity="phase" prop="registrationLimit" :autosave="300" classes="col-3"></entity-field>
                <entity-field :entity="phase" prop="registrationLimitPerOwner" :autosave="300" classes="col-3"></entity-field>
            </div>
        </div>

        <div class="col-12 sm:col-6">
            <opportunity-enable-claim></opportunity-enable-claim>
        </div>
        <template v-if="nextPhase?.__objectType != 'evaluationmethodconfiguration'">
            <div class="opportunity-data-collection__horizontal-line col-12 "></div>
            <opportunity-phase-publish-date-config :phase="phase" :phases="phases" hide-description hide-button></opportunity-phase-publish-date-config>
        </template>
        <div class="opportunity-data-collection__delete col-12" v-if="!phase.isLastPhase && !phase.isFirstPhase">
            <mc-confirm-button message="Confirma a execução da ação?" @confirm="deletePhase($event, phase, index)">
                <template #button="modal">
                    <a class="opportunity-data-collection__trash" @click="modal.open">
                        <div class="icon">
                            <mc-icon name="trash"></mc-icon>
                        </div>
                        <label class="label">{{ text('excluir_fase_coleta_dados') }}</label>
                    </a>
                </template>
            </mc-confirm-button>
        </div>
    </div>