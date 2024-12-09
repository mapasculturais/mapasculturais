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
');
?>

<div class="project-table">
    <entity-table type="project" identifier="projectTable" :query="query" :limit="100" :headers="headers" endpoint="find" required="name,type" :visible="visibleColumns" @clear-filters="clearFilters" @remove-filter="removeFilter($event)" :hide-filter="hideFilters" show-index>
        <template #actions="{entities}">
            <div class="project-table__actions">
                <h4 class="bold"><?= i::__('Ações:') ?></h4>
                <mc-export-spreadsheet :owner="owner" endpoint="entities" :params="{entityType: 'project', query}" group="entities-spreadsheets"></mc-export-spreadsheet>
            </div>
        </template>

        <template #filters="{entities,filters}">
            <div class="project-table__multiselects">
                <mc-multiselect class="col-2" :model="selectedType" :items="types" title="<?= i::esc_attr__('Selecione os tipos: ') ?>" @selected="filterByType(entities)" @removed="filterByType(entities)" :hide-filter="hideFilters" hide-button>
                    <template #default="{popover, setFilter, filter}">
                        <div class="field">
                            <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione o tipo: ') ?>">
                        </div>
                    </template>
                </mc-multiselect>
        
                <mc-multiselect class="col-2" :model="selectedSeals" :items="seals" title="<?php i::_e('Selecione os selos') ?>" @selected="filterBySeals(entities)" @removed="filterBySeals(entities)" :hide-filter="hideFilters" hide-button>
                    <template #default="{setFilter, popover}">
                        <div class="field">
                            <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione os selos') ?>">
                        </div>
                    </template>
                </mc-multiselect>
            </div>
            <div class="project-table__inputs">
                <div class="field--horizontal">
                    <label class="verified"><input v-model="verified" @change="getVerified()" :true-value="1" :false-value="undefined" type="checkbox"> <?php i::_e('Projetos oficiais') ?><mc-icon name="circle-checked"></mc-icon> </label>
                </div>
            </div>
        </template>
    </entity-table>
</div>