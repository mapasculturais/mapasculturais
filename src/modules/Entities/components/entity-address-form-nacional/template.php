<?php

use MapasCulturais\i;

$this->import('
    entity-map
');
?>

<div class="brasil-address-form">
    <?php $this->applyTemplateHook('entity-address-form-nacional', 'before'); ?>
    <div class="grid-12">
        <?php $this->applyTemplateHook('entity-address-form-nacional', 'begin'); ?>

        <!-- Formulário nacional -->
        <div class="col-12 grid-12">
            <!-- CEP -->
            <div class="field col-4 sm:col-12">
                <label :for="fid('postalCode')"><?= i::__('CEP') ?></label>
                <input
                    :id="fid('postalCode')"
                    type="text"
                    v-model.trim="entity.address_postalCode"
                    @blur="pesquisacep(entity.address_postalCode); address()"
                    placeholder="<?= i::__('00000-000') ?>" />
            </div>

            <!-- Logradouro (rua) -->
            <div class="field col-8 sm:col-12">
                <label :for="fid('street')"><?= i::__('Logradouro') ?></label>
                <input
                    :id="fid('street')"
                    type="text"
                    v-model.trim="addressStreet"
                    @blur="address()"
                    placeholder="<?= i::__('Rua, Avenida, Travessa…') ?>" />
            </div>

            <!-- Número -->
            <div class="field col-2 sm:col-4">
                <label :for="fid('number')"><?= i::__('Número') ?></label>
                <input
                    :id="fid('number')"
                    type="text"
                    v-model.trim="addressNumber"
                    @blur="address()"
                    placeholder="<?= i::__('Número') ?>" />
            </div>

            <!-- Bairro -->
            <div class="field col-10 sm:col-8">
                <label :for="fid('neighborhood')"><?= i::__('Bairro') ?></label>
                <input
                    :id="fid('neighborhood')"
                    type="text"
                    v-model.trim="entity.address_level6"
                    @blur="address()"
                    placeholder="<?= i::__('Bairro') ?>" />
            </div>

            <!-- Complemento -->
            <div class="field col-12 sm:col-6">
                <label :for="fid('line2')"><?= i::__('Complemento ou ponto de referência') ?></label>
                <input
                    :id="fid('line2')"
                    type="text"
                    v-model.trim="entity.address_line2"
                    @blur="address()"
                    placeholder="<?= i::__('Apartamento, bloco, ponto de referência…') ?>" />
            </div>

            <!-- Estado (UF) -->
            <div class="field col-6 sm:col-12">
                <label class="field__title" :for="fid('state')">
                    <?php i::_e('Estado') ?>
                </label>
                <select
                    :id="fid('state')"
                    @change="citiesList(); address()"
                    v-model="entity.address_level2">
                    <option disabled hidden :value="null"><?= i::__('Estado') ?></option>
                    <option v-for="state in states" :key="state.value" :value="state.value">
                        {{state.label}}
                    </option>
                </select>
            </div>

            <!-- Município -->
            <div class="field col-6 sm:col-12">
                <label class="field__title" :for="fid('city')">
                    <?php i::_e('Município') ?>
                </label>
                <select
                    :disabled="!entity.address_level2"
                    :id="fid('city')"
                    @change="address()"
                    v-model="entity.address_level4">
                    <option disabled hidden :value="null"><?= i::__('Cidade') ?></option>
                    <option v-for="city in cities" :key="city" :value="city">
                        {{city}}
                    </option>
                </select>
            </div>
        </div>

        <div class="col-12" v-if="hasPublicLocation">
            <div class="col-12 sm:col-12 field public-location">
                <div class="field__group">
                    <label class="field__checkbox">
                        <input type="checkbox" v-model="entity.publicLocation" />
                        <span>
                            <?= $this->text('public-location_adress', i::__('Localização pública'))?>
                            <?php $this->info('cadastro -> configuracoes-entidades -> localizacao-publica') ?>
                        </span>
                    </label>
                </div>

                <small class="field__description">
                    <?php i::_e('Marque o campo acima para tornar o endereço público ou deixe desmarcado para manter o endereço privado.') ?>
                </small>
            </div>
        </div>

        <div v-if="filledAdress()" class="col-12 grid-12">
            <p class="brasil-address-form__address col-12">
                <span v-if="entity.endereco">{{entity.endereco}}</span>
                <span v-else><?= i::_e("Sem Endereço"); ?></span>
            </p>
            <entity-map class="col-12" :entity="entity" editable></entity-map>
        </div>
    </div>
    <?php $this->applyTemplateHook('entity-address-form-nacional', 'end'); ?>
</div>