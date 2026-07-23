<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-select
    mc-multiselect
');
?>
<div class="opportunity-reports-filters">
    <mc-select class="opportunity-reports-filters__status" :options="statusOptions" :default-value="modelValue.status" placeholder="<?= i::esc_attr__('Status') ?>" @update:default-value="onStatusChange" small></mc-select>

    <mc-multiselect v-if="proponentTypeItems" class="opportunity-reports-filters__proponent-types" :model="selectedProponentTypes" :items="proponentTypeItems" placeholder="<?= i::esc_attr__('Tipo de proponente') ?>" @selected="onProponentTypesChange" @removed="onProponentTypesChange" hide-filter hide-button></mc-multiselect>

    <mc-multiselect v-if="rangeItems" class="opportunity-reports-filters__ranges" :model="selectedRanges" :items="rangeItems" placeholder="<?= i::esc_attr__('Faixa/Linha') ?>" @selected="onRangesChange" @removed="onRangesChange" hide-filter hide-button></mc-multiselect>
</div>
