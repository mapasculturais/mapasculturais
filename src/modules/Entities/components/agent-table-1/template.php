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
    <agent-table :agentType="1" :additionalHeaders="headers" :extra-query="extraQuery" :visible-columns="visibleColumns" @clear-filters="clearFilters" @remove-filter="removeFilter($event)" :hide-filters="hideFilters">
        <template #filters="{entities}">
            <mc-multiselect class="col-3 sm:col-4" :model="selectedSexualOrientation" :items="sexualOrientation" placeholder="<?= i::esc_attr__('Selecione a orientação sexual: ') ?>" @selected="filterByOrientacaoSexual(entities)" @removed="filterByOrientacaoSexual(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>

            <mc-multiselect class="col-3 sm:col-4" :model="selectedGender" :items="gender" placeholder="<?= i::esc_attr__('Selecione o gênero: ') ?>" @selected="filterByGender(entities)" @removed="filterByGender(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>

            <mc-multiselect class="col-3 sm:col-4" :model="selectedRace" :items="race" placeholder="<?= i::esc_attr__('Selecione raça/cor: ') ?>" @selected="filterByRace(entities)" @removed="filterByRace(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>
        </template>
        <template #advanced-filters="{entities}">
            <div clas="field">
                <label><input @click="oldPeopleFilter($event,entities)" ref="oldPeople" type="checkbox" name="agentType"> <?php i::_e('Pessoa Idosa') ?> </label>
            </div>
        </template>
    </agent-table>
</div>