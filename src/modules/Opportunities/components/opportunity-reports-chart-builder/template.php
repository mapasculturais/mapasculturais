<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-loading
    mc-modal
    opportunity-reports-chart-card
    opportunity-reports-chart-form
');
?>
<div class="opportunity-reports-chart-builder">
    <header class="opportunity-reports-chart-builder__header">
        <h3><?= i::__('Gráficos personalizados') ?></h3>
        <button type="button" class="button button--primary button--icon" @click="openCreateForm()">
            <mc-icon name="add"></mc-icon><?= i::__('Novo gráfico') ?>
        </button>
    </header>

    <mc-loading :condition="loading"></mc-loading>

    <div v-if="!loading" class="opportunity-reports-chart-builder__grid">
        <opportunity-reports-chart-card
            v-for="graphic in graphics"
            :key="graphic.reportData.graphicId"
            :title="graphic.reportData.title"
            :type="graphic.reportData.typeGraphic"
            :chart="graphic.data"
            editable
            deletable
            @edit="openEditForm(graphic)"
            @delete="removeGraphic(graphic.reportData.graphicId)"
        ></opportunity-reports-chart-card>
    </div>

    <mc-modal ref="formModal" :title="editingGraphic ? '<?= i::esc_attr__('Editar gráfico') ?>' : '<?= i::esc_attr__('Novo gráfico') ?>'">
        <template #default="{ close }">
            <opportunity-reports-chart-form
                :opportunity-id="opportunityId"
                :filters="filters"
                :available-fields="availableFields"
                :graphic="editingGraphic"
                @saved="onSaved(close)"
                @cancelled="close()"
            ></opportunity-reports-chart-form>
        </template>
    </mc-modal>
</div>
