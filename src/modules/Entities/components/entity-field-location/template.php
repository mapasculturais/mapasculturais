<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    entity-map
    mc-select
');
?>

<div class="entity-field-location">
    <div class="col-12">
        <div class="grid-12">
            <div class="field col-12">
                <label for="cep">
                    <?= i::__('CEP') ?>
                </label>
                <input @change="pesquisacep(entity[fieldName].En_CEP);" id="cep" type="text" v-maska data-maska="#####-###" v-model="entity[fieldName].En_CEP" />
            </div>

            <div class="field col-4">
                <label for="logradouro">
                    <?= i::__('Logradouro') ?>
                </label>
                <input id="logradouro" type="text" v-model="entity[fieldName].En_Nome_Logradouro" @change="entity.save()" />
            </div>

            <div class="field col-4">
                <label for="num">
                    <?= i::__('Número') ?>
                </label>
                <input id="num" type="number" v-model="entity[fieldName].En_Num" @change="entity.save()" />
            </div>

            <div class="field col-4">
                <label for="bairro">
                    <?= i::__('Bairro') ?>
                </label>
                <input id="bairro" type="text" v-model="entity[fieldName].En_Bairro" @change="entity.save()" />
            </div>

            <div class="field col-12">
                <label for="complemento">
                    <?= i::__('Complemento') ?>
                </label>
                <input id="complemento" type="text" v-model="entity[fieldName].En_Complemento" @change="entity.save()" />
            </div>

            <div v-if="statesAndCitiesCountryCode != 'BR'" class="field">
                <label for="country">
                    <?= i::__('País') ?>
                </label>
                <input id="country" type="text" v-model="entity[fieldName].En_Pais" @change="entity.save()" />
            </div>

            <div class="field col-6">
                <label for="field__title">
                    <?= i::__('Estado') ?>
                </label>
                <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>"  @change="citiesList(); address()" v-model:default-value="entity[fieldName].En_Estado" show-filter>
                    <option v-for="state in states" :value="state.value">{{state.label}}</option>
                </mc-select>
            </div>
            
            <div class="field col-6">
                <label for="field__title">
                    <?= i::__('Cidade') ?>
                </label>
                <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>"  @change="address()" v-model:default-value="entity[fieldName].En_Municipio" show-filter>
                    <option v-for="city in cities" :value="city">{{city}}</option>
                </mc-select>
            </div>
        </div>
    </div>
</div>