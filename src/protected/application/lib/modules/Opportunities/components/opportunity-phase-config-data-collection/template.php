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
        <entity-field :entity="entity" prop="registrationFrom" classes="col-6 sm:col-12" :min="getMinDate()" :max="getMaxDate()"></entity-field>
        <entity-field :entity="entity" prop="registrationTo" classes="col-6 sm:col-12" :min="entity.registrationFrom?._date" :max="getMaxDate()"></entity-field>
        <div class="config-phase__info col-12">
            <h5 class="config-phase__info--message">
                <mc-icon name="info"></mc-icon> <?= i::__("A configuração desse formulário está pendente") ?>
            </h5>
        </div>
        <div class="col-6 sm:col-12">
            <mc-link :entity="entity" route='formBuilder' class="config-phase__info--button button--primary button" icon="external">
              <?= i::__("Configurar formulário") ?>
            </mc-link>
        </div>

        <div class="config-phase__line-bottom col-12 "></div>
        <div class="phase-delete col-6" v-if="!entity.isLastPhase && !entity.isFirstPhase">
            <confirm-button message="Confirma a execução da ação?" @confirm="deletePhase($event, entity, currentIndex)">
                <template #button="modal">
                    <a class="phase-delete__trash" @click="modal.open">
                        <mc-icon name="trash"></mc-icon>
                        <label class="phase-delete__label">{{ text('excluir_fase_coleta_dados') }}</label>
                    </a>
                </template>
            </confirm-button>
        </div>
        <div class="phase-delete col-6">
            <a @click="entity.save()" class="phase-delete__trash " href="#"><mc-icon name="upload"></mc-icon><label class="phase-delete__label"><?= i::__("Salvar") ?></label></a>
        </div>
    </div>
</mapas-card>