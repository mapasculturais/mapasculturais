<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    opportunity-reports-filters
    opportunity-reports-static-charts
    opportunity-reports-chart-builder
');
?>
<div class="opportunity-reports">
    <opportunity-reports-filters v-model="filters"></opportunity-reports-filters>

    <opportunity-reports-static-charts :opportunity-id="entity.id" :filters="filters"></opportunity-reports-static-charts>

    <opportunity-reports-chart-builder :opportunity-id="entity.id" :filters="filters"></opportunity-reports-chart-builder>
</div>
