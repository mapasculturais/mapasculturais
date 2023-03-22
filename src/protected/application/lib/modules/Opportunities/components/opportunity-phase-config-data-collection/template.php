<?php
use MapasCulturais\i;
$this->import('
    confirm-button
');
?>

<mapas-card>
    <div class="config-phase grid-12">
        <div class="config-phase__line-up col-12 "></div>
        <div class="config-phase__title col-12">
            <h3 class="config-phase__title--title"><?= i::__("Configuração da fase") ?></h3>
        </div>
        <entity-field :entity="phase" prop="registrationFrom" :autosave="300" classes="col-6 sm:col-12" :min="minDate?._date" :max="phase.registrationTo?._date"></entity-field>
        <entity-field :entity="phase" prop="registrationTo" :autosave="300" classes="col-6 sm:col-12" :min="phase.registrationFrom?._date" :max="maxDate?._date"></entity-field>
        <div class="col-6 sm:col-12">
            <mc-link :entity="phase" route='formBuilder' class="config-phase__info--button button--primary button" icon="external">
              <?= i::__("Configurar formulário") ?>
            </mc-link>
        </div>

        <div class="config-phase__line-bottom col-12 "></div>
        <div class="phase-delete col-6" v-if="!phase.isLastPhase && !phase.isFirstPhase">
            <confirm-button message="Confirma a execução da ação?" @confirm="deletePhase($event, phase, index)">
                <template #button="modal">
                    <a class="phase-delete__trash" @click="modal.open">
                        <mc-icon name="trash"></mc-icon>
                        <label class="phase-delete__label">{{ text('excluir_fase_coleta_dados') }}</label>
                    </a>
                </template>
            </confirm-button>
        </div>
    </div>
</mapas-card>