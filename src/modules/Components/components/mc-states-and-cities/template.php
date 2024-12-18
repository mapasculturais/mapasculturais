<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>
<div>
    <div class="field">
        <label><?= i::__('Estados') ?></label>

        <mc-multiselect :model="selectedStates" title="<?php i::_e('Selecione os estados') ?>" :items="states" hide-filter hide-button>
            <template #default="{setFilter, popover}">
                <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Busque ou selecione') ?>">
            </template>
        </mc-multiselect>
        <mc-tag-list editable :tags="selectedStates" :labels="states" classes="agent__background agent__color"></mc-tag-list>
    </div>

    <div v-if="selectedStates.length > 0" class="field">
        <label><?= i::__('Cidades') ?></label>

        <mc-multiselect :model="selectedCities" title="<?php i::_e('Selecione as cidades') ?>" :items="cities" hide-filter hide-button>
            <template #default="{setFilter, popover}">
                <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" placeholder="<?= i::esc_attr__('Busque ou selecione') ?>">
            </template>
        </mc-multiselect>
        <mc-tag-list editable :tags="selectedCities" :labels="cities" classes="agent__background agent__color"></mc-tag-list>
    </div>
</div>