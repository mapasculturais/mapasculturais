<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;
?>

<div class="field" :class="fieldClass">
    <label v-if="!hideLabels"><?= i::__('Estados') ?></label>

    <mc-multiselect :model="selectedStates" title="<?php i::_e('Selecione os estados') ?>" :items="states" :placeholder="statePlaceholder" hide-filter hide-button></mc-multiselect>
    <mc-tag-list v-if="!hideTags" editable :tags="selectedStates" :labels="states" classes="agent__background agent__color"></mc-tag-list>
</div>

<div v-if="selectedStates.length > 0" class="field" :class="fieldClass">
    <label v-if="!hideLabels"><?= i::__('Cidades') ?></label>

    <mc-multiselect :model="selectedCities" title="<?php i::_e('Selecione as cidades') ?>" :items="cities" :placeholder="cityPlaceholder" hide-filter hide-button></mc-multiselect>
    <mc-tag-list v-if="!hideTags" editable :tags="selectedCities" :labels="cities" classes="agent__background agent__color"></mc-tag-list>
</div>