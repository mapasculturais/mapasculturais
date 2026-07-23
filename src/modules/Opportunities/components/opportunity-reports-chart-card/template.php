<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-card
    mc-icon
    mc-chart
');
?>
<mc-card class="opportunity-reports-chart-card">
    <header class="opportunity-reports-chart-card__header">
        <h4 class="opportunity-reports-chart-card__title">{{ title }}</h4>

        <div class="opportunity-reports-chart-card__actions">
            <a v-if="exportUrl" :href="exportUrl" download class="opportunity-reports-chart-card__export" :title="<?= i::esc_attr__('Exportar CSV') ?>">
                <mc-icon name="download"></mc-icon>
            </a>
            <button v-if="editable" type="button" class="opportunity-reports-chart-card__edit" @click="$emit('edit')" :title="<?= i::esc_attr__('Editar') ?>">
                <mc-icon name="edit"></mc-icon>
            </button>
            <button v-if="deletable" type="button" class="opportunity-reports-chart-card__delete" @click="$emit('delete')" :title="<?= i::esc_attr__('Excluir') ?>">
                <mc-icon name="delete"></mc-icon>
            </button>
        </div>
    </header>

    <mc-chart :type="type" :labels="chart.labels" :datasets="chart.datasets" :data="chart.data" :background-color="chart.backgroundColor"></mc-chart>
</mc-card>
