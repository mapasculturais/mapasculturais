<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-table
    mc-icon
    mc-export-spreadsheet
    mc-states-and-cities
');
?>

<div class="agent-table">
    <entity-table type="agent" identifier="agentTable" :query="mergedQuery" :headers="headers" endpoint="find" required="name,type" :visible="visibleColumns" @clear-filters="clearFilters" @remove-filter="removeFilter($event)" :hide-filter="hideFilters" show-index>
        <template #actions="{entities, spreadsheetQuery}">
            <div class="agent-table__actions">
                <h4 class="bold"><?= i::__('Ações:') ?></h4>
                <mc-export-spreadsheet :owner="owner" endpoint="entities" :params="{entityType: 'agent', query: spreadsheetQuery}" group="entities-spreadsheets"></mc-export-spreadsheet>
            </div>
        </template>

        <template #filters="{entities}">
            <div class="agent-table__multiselects grid-12">
                <mc-multiselect class="col-3 sm:col-4" :model="selectedArea" :items="terms" placeholder="<?= i::esc_attr__('Selecione as áreas: ') ?>" @selected="filterByArea(entities)" @removed="filterByArea(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>

                <mc-multiselect class="col-3 sm:col-4" :model="selectedSeals" :items="seals" placeholder="<?= i::esc_attr__('Selecione os selos: ') ?>" @selected="filterBySeals(entities)" @removed="filterBySeals(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>

                <mc-states-and-cities field-class="col-3 sm:col-4" hide-labels hide-tags v-model:model-states="selectedStates" v-model:model-cities="selectedCities" @changeCities="filterByCities(entities)" @changeStates="filterByState(entities)"></mc-states-and-cities>

                <div class="agent-table__inputs col-3 sm:col-4">
                    <div class="field">
                        <input type="text" v-model="selectedBairro" @input="filterByBairro(entities)" placeholder="<?= i::__('Digite o bairro') ?>">
                    </div>
                </div>

                <slot name="filters" v-bind="{entities}"></slot>
            </div>

            <div class="agent-table__inputs">
                <div class="field--horizontal">
                    <label class="verified"><input v-model="verified" @change="getVerified()" :true-value="1" :false-value="undefined" type="checkbox"> <?php i::_e('Agentes oficiais') ?> <mc-icon name="circle-checked"></mc-icon></label>
                </div>
            </div>
        </template>

        <template v-if="hasSlot('advanced-filters')" #advanced-filters="{entities}">
            <div class="custom-advanced-filters">
                <slot name="advanced-filters" v-bind="{entities}"></slot>
            </div>
        </template>
    </entity-table>
</div>
