<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-modal
');
?>
<mc-modal title="<?= i::__('Exportar oportunidade') ?>">
    <template #default="modal">
        <div class="opportunity-exporter">
            <p>
                <?= i::__('Selecione os dados que serão exportados:') ?>
                <?php $this->info('editais-oportunidades -> configuracoes -> exportando-oportunidade') ?>
            </p>
            <div class="field field__group">
                <label class="field__checkbox">
                    <input type="checkbox" name="infos" v-model="filters.infos">
                    <span><?= i::__('Informações básicas') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="files" v-model="filters.files">
                    <span><?= i::__('Anexos') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="images" v-model="filters.images">
                    <span><?= i::__('Imagens') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="dates" v-model="filters.dates">
                    <span><?= i::__('Datas das fases') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="vacancyLimits" v-model="filters.vacancyLimits">
                    <span><?= i::__('Limites de vagas') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="workplan" v-model="filters.workplan">
                    <span><?= i::__('Plano de metas') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="statusLabels" v-model="filters.statusLabels">
                    <span><?= i::__('Configurações de status') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="phaseSeals" v-model="filters.phaseSeals">
                    <span><?= i::__('Selos certificadores') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="appealPhases" v-model="filters.appealPhases">
                    <span><?= i::__('Fases de recurso') ?></span>
                </label>
                <label class="field__checkbox">
                    <input type="checkbox" name="monitoringPhases" v-model="filters.monitoringPhases">
                    <span><?= i::__('Fases de monitoramento') ?></span>
                </label>
            </div>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="cancelExport(modal)"><?= i::__('Cancelar') ?></button>
        <button class="button button--primary" @click="doExport(modal)"><?= i::__('Exportar') ?></button>
    </template>

    <template #button="modal">
        <button type="button" class="button button--icon button--sm" @click="modal.open()"><?= i::__('Exportar') ?></button>
    </template>
</mc-modal>