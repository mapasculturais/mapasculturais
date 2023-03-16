<?php
use MapasCulturais\i;
$this->import('
    confirm-button
');
?>

<mapas-card>
    <div class="evaluation-step grid-12">
        <div class="config-input col-12">
            <entity-field :entity="phase" prop="name" :autosave="300" label="<?= i::esc_attr__('Título') ?>" hide-required></entity-field>
        </div>

        <entity-field :entity="phase" prop="evaluationFrom" :autosave="300" classes="col-6 sm:col-12" :min="minDate?._date" :max="phase.evaluationTo?._date"></entity-field>
        <entity-field :entity="phase" prop="evaluationTo" :autosave="300" classes="col-6 sm:col-12" :min="phase.evaluationFrom?._date" :max="maxDate?._date"></entity-field>


        <div class="evaluation-text col-12">
            <h3><?= i::__("Adicionar textos explicativos das avaliações") ?></h3>
        </div>
        <div class="evaluation-config col-12 field">
            <label> <?= i::__("Texto configuração geral") ?>
            </label>
<!--            <entity-field :entity="phase" prop="infos"></entity-field>-->
                <textarea v-model="phase.infos['general']" class="evaluation-config__area" rows="10"></textarea>
        </div>
        <div class="col-6 sm:col-12 field" v-for="(category, index) in categories">
            <label :key="index"> {{ category }}
<!--                <entity-field :entity="phase" prop="infos[category]"></entity-field>-->
                <textarea v-model="phase.infos[category]" style="width: 100%" rows="10"></textarea>
            </label>
        </div>
        <div class="config-phase__line-bottom col-12"></div>
        <div class="phase-delete col-6">
            <confirm-button message="<?= i::esc_attr__('Confirma a execução da ação?')?>" @confirm="deletePhase($event, phase, index)">
                <template #button="modal">
                    <a class="phase-delete__trash" @click="modal.open()">
                        <mc-icon name="trash"></mc-icon>
                        <label class="phase-delete__label"><?= i::__("Excluir fase de avaliação") ?></label>
                    </a>
                </template>
            </confirm-button>
        </div>
    </div>
</mapas-card>
