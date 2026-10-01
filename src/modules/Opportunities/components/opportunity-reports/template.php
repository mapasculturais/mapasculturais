<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    opportunity-reports-filters
    opportunity-reports-static-charts
    opportunity-reports-chart-builder
');
?>
<div class="opportunity-reports">
    <header class="opportunity-reports__print-header">
        <h1>{{ entity.name }}</h1>
        <p v-if="phaseLabel">{{ phaseLabel }}</p>
    </header>

    <div class="opportunity-reports__toolbar">
        <opportunity-reports-filters v-model="filters"></opportunity-reports-filters>
        <button type="button" class="button button--primary button--icon" :disabled="printing" @click="print()">
            <mc-icon name="print"></mc-icon> <?= i::__('Imprimir') ?>
        </button>
    </div>

    <opportunity-reports-static-charts :opportunity-id="entity.id" :filters="filters"></opportunity-reports-static-charts>

    <opportunity-reports-chart-builder :opportunity-id="entity.id" :filters="filters"></opportunity-reports-chart-builder>
</div>
