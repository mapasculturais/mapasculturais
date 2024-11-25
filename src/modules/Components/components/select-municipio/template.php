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
        <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>" v-model:default-value="selectedState" @change-option="loadCities($event)" show-filter>
            <option v-for="state in states" :key="state.sigla" :value="state.sigla">{{state.nome}}</option>
        </mc-select>
    </div>
    
    <div v-if="selectedState" class="field col-6">
        <label class="field__title">
            <?=i::__('Município') ?>
        </label>
        <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>" v-model:default-value="selectedCity" @change-option="selectCity($event)" show-filter>
            <option v-for="city in cities" :key="city.nome" :value="city.nome">{{city.nome}}</option>
        </mc-select>
    </div>
</div>
