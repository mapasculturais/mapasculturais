<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-file
    mc-modal
');
?>
<mc-modal title="<?= i::__('Importar oportunidade') ?>">
    <template #default="modal">
        <div class="opportunity-exporter">
            <template v-if="!opportunity">
                <p><?= i::__('Selecione o arquivo que será importado:') ?></p>
                <mc-file accept=".json" @file-selected="parseFile"></mc-file>
            </template>
            <template v-if="opportunity">
                <p>
                    <?= i::__('Selecione os dados que serão importados:') ?>
                    <?php $this->info('editais-oportunidades -> configuracoes -> exportando-oportunidade') ?>
                </p>
                <div class="field field__group">
                    <label class="field__checkbox">
                        <input type="checkbox" name="infos" :disabled="!availableFilters.infos" v-model="filters.infos">
                        <span><?= i::__('Informações básicas') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="files" :disabled="!availableFilters.files" v-model="filters.files">
                        <span><?= i::__('Anexos') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="images" :disabled="!availableFilters.images" v-model="filters.images">
                        <span><?= i::__('Imagens') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="dates" :disabled="!availableFilters.dates" v-model="filters.dates">
                        <span><?= i::__('Datas das fases') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="vacancyLimits" :disabled="!availableFilters.vacancyLimits" v-model="filters.vacancyLimits">
                        <span><?= i::__('Limites de vagas') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="categories" :disabled="!availableFilters.categories" v-model="filters.categories">
                        <span><?= i::__('Categorias') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="ranges" :disabled="!availableFilters.ranges" v-model="filters.ranges">
                        <span><?= i::__('Faixas/Linhas') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="proponentTypes" :disabled="!availableFilters.proponentTypes" v-model="filters.proponentTypes">
                        <span><?= i::__('Tipos de proponente') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="workplan" :disabled="!availableFilters.workplan" v-model="filters.workplan">
                        <span><?= i::__('Plano de metas') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="statusLabels" :disabled="!availableFilters.statusLabels" v-model="filters.statusLabels">
                        <span><?= i::__('Configurações de status') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="phaseSeals" :disabled="!availableFilters.phaseSeals" v-model="filters.phaseSeals">
                        <span><?= i::__('Selos certificadores') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="appealPhases" :disabled="!availableFilters.appealPhases" v-model="filters.appealPhases">
                        <span><?= i::__('Fases de recurso') ?></span>
                    </label>
                    <label class="field__checkbox">
                        <input type="checkbox" name="monitoringPhases" :disabled="!availableFilters.monitoringPhases" v-model="filters.monitoringPhases">
                        <span><?= i::__('Fases de monitoramento') ?></span>
                    </label>
                </div>
            </template>
        </div>
    </template>

    <template #actions="modal">
        <button class="button button--text" @click="cancelImport(modal)"><?= i::__('Cancelar') ?></button>
        <button class="button button--primary" @click="doImport(modal)"><?= i::__('Importar') ?></button>
    </template>

    <template #button="modal">
        <button type="button" class="button button--solid button--icon" @click="modal.open()">
            <mc-icon name="add"></mc-icon>
            <?= i::__('Importar oportunidade') ?>
        </button>
    </template>
</mc-modal>