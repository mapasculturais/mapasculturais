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

            <div class="field col-4">
                <label for="cep">
                    <?= i::__('CEP') ?>
                </label>
                <input @change="pesquisacep(addressData.En_CEP, true);" id="cep" type="text" v-maska data-maska="#####-###" v-model="addressData.En_CEP" />
            </div>

            <div class="field col-8">
                <label for="logradouro">
                    <?= i::__('Logradouro') ?>
                </label>
                <input id="logradouro" type="text" v-model="addressData.En_Nome_Logradouro" @change="save" />
            </div>

            <div class="field col-6">
                <label for="num">
                    <?= i::__('Número') ?>
                </label>
                <input id="num" type="number" v-model="addressData.En_Num" @change="save" />
            </div>

            <div class="field col-6">
                <label for="bairro">
                    <?= i::__('Bairro') ?>
                </label>
                <input id="bairro" type="text" v-model="addressData.En_Bairro" @change="save" />
            </div>

            <div class="field col-12 sm:col-12">
                <label for="complemento">
                    <?= i::__('Complemento') ?>
                </label>
                <input id="complemento" type="text" v-model="addressData.En_Complemento" @change="save" />
            </div>

            <div v-if="statesAndCitiesCountryCode != 'BR'" class="field">
                <label for="country">
                    <?= i::__('País') ?>
                </label>
                <input id="country" type="text" v-model="addressData.En_Pais" @change="save" />
            </div>

            <div class="field col-6">
                <label for="field__title">
                    <?= i::__('Estado') ?>
                </label>
                <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>"  @change="citiesList(); address()" v-model:default-value="addressData.En_Estado" show-filter>
                    <option v-for="state in states" :value="state.value">{{state.label}}</option>
                </mc-select>
            </div>
            
            <div v-if="addressData.En_Estado" class="field col-6">
                <label for="field__title">
                    <?= i::__('Cidade') ?>
                </label>
                <mc-select placeholder="<?= i::esc_attr_e("Selecione"); ?>"  @change="address()" v-model:default-value="addressData.En_Municipio" show-filter>
                    <option v-for="city in cities" :value="city">{{city}}</option>
                </mc-select>
            </div>

            <div v-if="configs?.setPrivacy" class="field col-12">
                <label>
                    <?= $this->text('privacy-label', i::__('Este endereço pode ficar público na plataforma?')) ?>
                </label>
                <div class="field__group">
                    <label for="publicLocationYes" class="input__radioLabel">
                        <input type="radio" id="publicLocationYes" v-model="addressData.publicLocation" value="true" @change="save()" />
                        <?= i::__('Sim. Estou ciente de que este endereço aparecerá na plataforma no perfil do agente coletivo vinculado a esta inscrição.') ?>
                    </label>
                    <label for="publicLocationNo" class="input__radioLabel">
                        <input type="radio" id="publicLocationNo" v-model="addressData.publicLocation" value="false" @change="save()"/>
                        <?= i::__('Não.') ?>
                    </label>
                </div>
            </div>
        </div>
    </div>
</div>