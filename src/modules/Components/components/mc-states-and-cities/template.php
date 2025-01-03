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

        <mc-multiselect :model="selectedStates" title="<?php i::_e('Selecione os estados') ?>" :items="states" placeholder="<?= i::esc_attr__('Busque ou selecione') ?>" hide-filter hide-button></mc-multiselect>
        <mc-tag-list editable :tags="selectedStates" :labels="states" classes="agent__background agent__color"></mc-tag-list>
    </div>

    <div v-if="selectedStates.length > 0" class="field">
        <label><?= i::__('Cidades') ?></label>

        <mc-multiselect :model="selectedCities" title="<?php i::_e('Selecione as cidades') ?>" :items="cities" placeholder="<?= i::esc_attr__('Busque ou selecione') ?>" hide-filter hide-button></mc-multiselect>
        <mc-tag-list editable :tags="selectedCities" :labels="cities" classes="agent__background agent__color"></mc-tag-list>
    </div>
</div>