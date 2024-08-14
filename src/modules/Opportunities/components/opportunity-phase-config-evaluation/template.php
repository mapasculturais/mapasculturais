<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    mc-confirm-button
    mc-modal
    opportunity-evaluation-committee
    opportunity-phase-publish-date-config
    tiebreaker-criteria-configuration
    v1-embed-tool

    affirmative-policies--geo-quota-configuration
');

$evaluation_methods = $app->getRegisteredEvaluationMethods();
?>
<mc-card> 
    <div class="evaluation-step grid-12">

        <section class="col-12 evaluation-step__section">
            <div class="evaluation-step__section-content">
                <div class="grid-12">
                    <entity-field :entity="phase" prop="name" :autosave="3000" classes="col-12" label="<?= i::esc_attr__('Título') ?>" hide-required></entity-field>
                    <entity-field :entity="phase" prop="evaluationFrom" :autosave="3000" classes="col-6 sm:col-12" label="<?= i::esc_attr__('Data de início') ?>" :min="fromDateMin?._date" :max="fromDateMax?._date"></entity-field>    
                    <entity-field :entity="phase" prop="evaluationTo" :autosave="3000" classes="col-6 sm:col-12" label="<?= i::esc_attr__('Data de término') ?>" :min="toDateMin?._date" :max="toDateMax?._date"></entity-field>
                </div>
            </div>
        </section>

        <?php foreach($evaluation_methods as $evaluation_method): ?>
            <?php $this->applyComponentHook("{$evaluation_method->slug}-config", 'before') ?>
            <template v-if="phase.type.id == '<?=$evaluation_method->slug?>'">
                <?php $this->applyComponentHook("{$evaluation_method->slug}-config", 'begin') ?>
                <?= $this->part("{$evaluation_method->slug}/phase-config") ?>
                <?php $this->applyComponentHook("{$evaluation_method->slug}-config", 'end') ?>
            </template>
            <?php $this->applyComponentHook("{$evaluation_method->slug}-config", 'after') ?>
        <?php endforeach; ?>

        <section class="col-12 evaluation-step__section">
            <div class="evaluation-step__section-header">
                <div class="evaluation-step__section-label">
                    <h3><?= i::__('Comissão de avaliação') ?></h3>
                </div>
            </div>

            <div class="evaluation-step__section-content">
                <opportunity-evaluation-committee :entity="phase"></opportunity-evaluation-committee>
                <v1-embed-tool v-if="phase.type.id == 'qualification'" route="evaluationmanager" :id="phase.opportunity.id"></v1-embed-tool>
            </div>
        </section>

        <section class="evaluation-section col-12">
            <div class="evaluation-section__header">
                <span class="title"><?= i::__('Configurar campos visíveis para avaliação') ?></span>
                <span class="subtitle"><?= i::__('Defina quais campos serão habilitados para avaliação.') ?></span>
            </div>

            <mc-modal title="<?= i::esc_attr__('Configurar campos visíveis para os avaliadores')?>" classes="modalEmbedTools">
                <template #default="modal">
                    <v1-embed-tool route="fieldsvisible" :id="phase.opportunity.id"></v1-embed-tool>
                </template>
                <template #button="modal">
                    <button class="evaluation-fields-button button button--bg button--secondarylight" @click="modal.open"><?= i::__('Abrir campos') ?></button>
                </template>
            </mc-modal>
        </section>

        <section class="evaluation-section col-12">
            <div class="evaluation-section__header">
                <span class="title"><?= i::__("Adicionar textos explicativos das avaliações") ?></span>
            </div>

            <div class="field evaluation-section__field">
                <label for="field-info-general" class="evaluation-section__label semibold"><?= i::__("Texto configuração geral") ?></label>
                <textarea id="field-info-general" v-model="phase.infos['general']" @change="savePhase()" class="evaluation-config__area" rows="10"></textarea>
            </div>
        </section>

        <div class="col-6 sm:col-12 field evaluation-section__field" v-for="(category, index) in categories">
            <label :for="`field-info-${category}`" class="evaluation-section__label semibold" :key="index"> {{ category }}</label>
            <textarea :id="`field-info-${category}`" v-model="phase.infos[category]" @change="savePhase()" style="width: 100%" rows="10" class="evaluation-config__input"></textarea>
        </div>

        <opportunity-phase-publish-date-config :phase="phase.opportunity" :phases="phases" hide-button hide-description></opportunity-phase-publish-date-config>
    
        <div class="config-phase__line col-12"></div>

        <div class="col-12 sm:col-12">
            <?php $this->applyComponentHook('bottom') ?>
        </div>

        <div class="phase-delete col-12">
            <mc-confirm-button message="<?= i::esc_attr__('Confirma a execução da ação?')?>" @confirm="deletePhase($event, phase, index)">
                <template #button="modal">
                    <button :class="['phase-delete__trash button button--text button--sm', {'disabled' : !phase.currentUserPermissions.remove}]" @click="modal.open()">
                        <div class="icon">
                            <mc-icon name="trash" class="secondary__color"></mc-icon> 
                        </div>
                        <h5><?= i::__("Excluir fase de avaliação") ?></h5>
                    </button>
                </template>
            </mc-confirm-button>
        </div>

    </div>
</mc-card>
