<?php
use MapasCulturais\i;
$this->import('
    confirm-button
    v1-embed-tool
    opportunity-phase-publish-date-config
');
?>

<mapas-card>
    <div class="evaluation-step grid-12">

        <section class="evaluation-section col-12 grid-12">
            <entity-field :entity="phase" prop="name" :autosave="300" classes="col-12" label="<?= i::esc_attr__('Título') ?>" hide-required></entity-field>
            <entity-field :entity="phase" prop="evaluationFrom" :autosave="300" classes="col-6 sm:col-12" label="<?= i::esc_attr__('Data de início') ?>" :min="minDate?._date" :max="phase.evaluationTo?._date"></entity-field>    
            <entity-field :entity="phase" prop="evaluationTo" :autosave="300" classes="col-6 sm:col-12" label="<?= i::esc_attr__('Data de término') ?>" :min="phase.evaluationFrom?._date" :max="maxDate?._date"></entity-field>
        </section>

        <div class="evaluation-line col-12"></div>
        
        <section class="evaluation-section col-12">
            <v1-embed-tool route="evaluationmanager" :id="phase.opportunity.id"></v1-embed-tool>
        </section>

        <section class="evaluation-section col-12">
            <div class="evaluation-section__header">
                <span class="title"><?= i::__('Configurar campos visíveis para avaliação') ?></span>
                <span class="subtitle"><?= i::__('Defina quais campos serão habilitados para avaliação.') ?></span>
            </div>

            <modal title="<?= i::esc_attr__('Configurar campos visíveis para os avaliadores')?>" classes="modalEmbedTools">
                <template #default="modal">
                    <v1-embed-tool route="fieldsvisible" :id="phase.opportunity.id"></v1-embed-tool>
                </template>
                <template #button="modal">
                    <button class="evaluation-fields-button button button--bg button--secondarylight" @click="modal.open"><?= i::__('Abrir campos') ?></button>
                </template>
            </modal>  

        </section>

        <section class="evaluation-section col-12">
            <div class="evaluation-section__header">
                <span class="title"><?= i::__("Adicionar textos explicativos das avaliações") ?></span>
            </div>

            <div class="field">
                <label><?= i::__("Texto configuração geral") ?></label>
                <textarea v-model="phase.infos['general']" @change="savePhase()" class="evaluation-config__area" rows="10"></textarea>
            </div>
        </section>        

        

        <div class="col-6 sm:col-12 field" v-for="(category, index) in categories">
            <label :key="index"> {{ category }}
                <textarea v-model="phase.infos[category]" @change="savePhase()" style="width: 100%" rows="10"></textarea>
            </label>
        </div>


<!--        <opportunity-phase-publish-date-config :phase="phase.opportunity" :phases="phases" hide-button hide-description></opportunity-phase-publish-date-config>-->
    
        <div class="config-phase__line col-12"></div>

        <div class="phase-delete col-12">
            <confirm-button message="<?= i::esc_attr__('Confirma a execução da ação?')?>" @confirm="deletePhase($event, phase, index)">
                <template #button="modal">
                    <a class="phase-delete__trash" @click="modal.open()">
                        <mc-icon name="trash"></mc-icon> <label class="phase-delete__label"><?= i::__("Excluir fase de avaliação") ?></label>
                    </a>
                </template>
            </confirm-button>
        </div>

    </div>
</mapas-card>
