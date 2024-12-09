<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-table
    mc-icon
    agent-table
');
?>

<div class="agent-table-1">
    <agent-table :agentType=1 :additionalHeaders="headers" :extra-query="extraQuery" :visible-columns="visibleColumns" @clear-filters="clearFilters" @remove-filter="removeFilter($event)" :hide-filters="hideFilters">
        <template #filters="{entities}">
            <mc-multiselect class="col-3 sm:col-4" :model="selectedSexualOrientation" :items="sexualOrientation" title="<?= i::esc_attr__('Selecione a orientação sexual: ') ?>" @selected="filterByOrientacaoSexual(entities)" @removed="filterByOrientacaoSexual(entities)" :hide-filter="hideFilters" hide-button>
                <template #default="{popover, setFilter, filter}">
                    <div class="field">
                        <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione a orientação sexual: ') ?>">
                    </div>
                </template>
            </mc-multiselect>

            <mc-multiselect class="col-3 sm:col-4" :model="selectedGender" :items="gender" title="<?= i::esc_attr__('Selecione o gênero: ') ?>" @selected="filterByGender(entities)" @removed="filterByGender(entities)" :hide-filter="hideFilters" hide-button>
                <template #default="{popover, setFilter, filter}">
                    <div class="field">
                        <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione o gênero: ') ?>">
                    </div>
                </template>
            </mc-multiselect>

            <mc-multiselect class="col-3 sm:col-4" :model="selectedRace" :items="race" title="<?= i::esc_attr__('Selecione raça/cor: ') ?>" @selected="filterByRace(entities)" @removed="filterByRace(entities)" :hide-filter="hideFilters" hide-button>
                <template #default="{popover, setFilter, filter}">
                    <div class="field">
                        <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione raça/cor: ') ?>">
                    </div>
                </template>
            </mc-multiselect>
        </template>
        <template #advanced-filters="{entities}">
            <div clas="field">
                <label><input @click="oldPeopleFilter($event,entities)" ref="oldPeople" type="checkbox" name="agentType"> <?php i::_e('Pessoa Idosa') ?> </label>
            </div>
        </template>
    </agent-table>
</div>