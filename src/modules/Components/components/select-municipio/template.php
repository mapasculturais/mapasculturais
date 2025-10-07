<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-select
');
?>

<div class="select-municipio">
    <div class="field col-6">
        <label class="field__title">
            <?= i::__('Estado') ?>
        </label>
        <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>" v-model:default-value="selectedState" @change-option="loadCities($event)" show-filter :options="states.map(state => ({value: state.sigla, label: state.nome}))"></mc-select>
    </div>
    
    <div v-show="selectedState" class="field col-6">
        <label class="field__title">
            <?=i::__('Município') ?>
        </label>
        <mc-select :key="'city-select-' + selectedState" placeholder="<?= i::esc_attr_e("Selecione"); ?>" v-model:default-value="selectedCity" @change-option="selectCity($event)" show-filter :options="cities.map(city => ({value: city.nome, label: city.nome}))"></mc-select>
    </div>
</div>