<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div class="mc-chart">
    <table v-if="type === 'table'" class="mc-chart__table">
        <thead>
            <tr>
                <th></th>
                <th v-for="(label, index) in labels" :key="index">{{ label }}</th>
            </tr>
        </thead>
        <tbody>
            <tr v-for="(dataset, index) in chartData.datasets" :key="index">
                <th>{{ dataset.label }}</th>
                <td v-for="(value, valueIndex) in dataset.data" :key="valueIndex">{{ value }}</td>
            </tr>
        </tbody>
    </table>
    <component v-else :is="chartComponent" :data="chartData" :options="chartOptions" />
</div>
