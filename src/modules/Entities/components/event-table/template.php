<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-table
    mc-icon
    mc-multiselect
    mc-export-spreadsheet
');
?>

<div class="event-table">
    <entity-table type="event" identifier="eventTable" :query="query" :limit="100" :headers="headers" endpoint="find" required="name" :visible="visible" @clear-filters="clearFilters" @remove-filter="removeFilter($event)" :hide-filter="hideFilters" show-index>
        <template #actions="{entities, spreadsheetQuery}">
            <div class="event-table__actions">
                <h4 class="bold"><?= i::__('Ações:') ?></h4>
                <mc-export-spreadsheet :owner="owner" endpoint="entities" :params="{entityType: 'event', query: spreadsheetQuery}" group="entities-spreadsheets"></mc-export-spreadsheet>
            </div>
        </template>

        <template #filters="{entities,filters}">
            <div class="event-table__multiselects">
                <mc-multiselect class="col-2" :model="selectedLinguagem" :items="terms" placeholder="<?php i::_e('Selecione as linguagens') ?>" @selected="filterByLinguagem(entities)" @removed="filterByLinguagem(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>

                <mc-multiselect class="col-2" :model="selectedAgeRating" :items="ageRating" placeholder="<?php i::_e('Classificação Etária') ?>" @selected="filterByAgeRating(entities)" @removed="filterByAgeRating(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>

                <mc-multiselect class="col-2" :model="selectedSeals" :items="seals" placeholder="<?php i::_e('Selecione os selos') ?>" @selected="filterBySeals(entities)" @removed="filterBySeals(entities)" :hide-filter="hideFilters" hide-button></mc-multiselect>
            </div>
        </template>
    </entity-table>
</div>
