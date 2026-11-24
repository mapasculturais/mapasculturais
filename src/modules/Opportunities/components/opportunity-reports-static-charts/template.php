<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    mc-loading
    opportunity-reports-chart-card
');
?>
<div class="opportunity-reports-static-charts">
    <mc-loading :condition="loading"></mc-loading>

    <div v-if="!loading" class="opportunity-reports-static-charts__grid">
        <opportunity-reports-chart-card v-for="card in chartCards" :key="card.key" :title="card.title" :type="card.type" :chart="card.chart" :export-url="card.exportUrl"></opportunity-reports-chart-card>
    </div>
</div>
