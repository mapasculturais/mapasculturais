<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field
    entity-map
');
?>

<div class="international-address-form">
    <?php $this->applyTemplateHook('entity-address-form-internacional', 'before'); ?>
    <div class="grid-12">
        <?php $this->applyTemplateHook('entity-address-form-internacional', 'begin'); ?>

        <!-- Código postal -->
        <div class="field col-12 sm:col-6" :class="{'field--error': hasError('address_postalCode')}">
            <label :for="fid('postalCode')">
                <?= i::__('Código postal') ?>
                <span v-if="isRequired('address_postalCode')" class="required">*</span>
            </label>
            <input
                :id="fid('postalCode')"
                type="text"
                v-model.trim="entity.address_postalCode"
                @blur="address()"
                placeholder="<?= i::__('Código postal') ?>" />
        </div>

        <!-- Seletores hierárquicos (quando existe hierarchy) -->
        <template v-for="i in 6" :key="`sel-${i}`">
            <div
                v-if="getLevel(i-1) && showSubLevelSelect(getLevel(i-1), i-1)"
                class="field col-6 sm:col-12"
                :class="{'field--error': hasError(addressKeyForLevel(i))}">
                <label
                    class="field__title"
                    :for="fid(`level${i}`)">
                    {{ fieldLabel(i) }}
                    <span v-if="isRequired(addressKeyForLevel(i))" class="required">*</span>
                </label>

                <select
                    :id="fid(`level${i}`)"
                    v-model.number="selectedLevels[i]"
                    @change="clearSubLevels(i); address();">
                    <option
                        v-for="(lvl, idx) in getLevel(i-1).subLevels"
                        :key="`lvl-${i}-${idx}`"
                        :value="idx">
                        {{ lvl.label }}
                    </option>
                </select>
            </div>
        </template>

        <!-- Inputs livres para níveis (quando NÃO há hierarchy) -->
        <template v-if="!levelHierarchy">
            <template v-for="(enabled, lvlKey) in activeLevels" :key="`free-${lvlKey}`">
                <div
                    v-if="enabled && !getLevel(toLevelNum(lvlKey))"
                    class="field sm:col-12"
                    :class="[
                        getColumnClass(toLevelNum(lvlKey), Object.keys(activeLevels)),
                        {'field--error': hasError(addressKeyForLevel(toLevelNum(lvlKey)))}
                    ]"
                >
                    <label class="field__title" :for="fid(`level${toLevelNum(lvlKey)}`)">
                        {{ fieldLabel(toLevelNum(lvlKey)) }}
                        <span v-if="isRequired(addressKeyForLevel(toLevelNum(lvlKey)))" class="required">*</span>
                    </label>
                    <input
                        :id="fid(`level${toLevelNum(lvlKey)}`)"
                        type="text"
                        v-model.trim="entity[`address_level${toLevelNum(lvlKey)}`]"
                        @blur="address()"
                        :placeholder="fieldLabel(toLevelNum(lvlKey))"
                    />
                </div>
            </template>
        </template>

        <div class="field col-12 sm:col-6" :class="{'field--error': hasError('address_line1')}">
            <label :for="fid('line1')">
                <?= i::__('Endereço') ?>
                <span v-if="isRequired('address_line1') || isRequired('address_number')" class="required">*</span>
            </label>
            <input
                :id="fid('line1')"
                type="text"
                v-model.trim="entity.address_line1"
                @blur="address()"
                placeholder="<?= i::__('Endereço (rua, número, etc.)') ?>" />
        </div>

        <div class="field col-12 sm:col-6" :class="{'field--error': hasError('address_line2')}">
            <label :for="fid('line2')">
                <?= i::__('Complemento') ?>
                <span v-if="isRequired('address_line2')" class="required">*</span>
            </label>
            <input
                :id="fid('line2')"
                type="text"
                v-model.trim="entity.address_line2"
                @blur="address()"
                placeholder="<?= i::__('Complemento') ?>" />
        </div>

        <!-- Localização pública -->
        <div class="col-12" v-if="hasPublicLocation">
            <div class="col-12 sm:col-12 field public-location">
                <div class="field__group">
                    <label class="field__checkbox">
                        <input type="checkbox" v-model="entity.publicLocation" />
                        <span>
                            <?= $this->text('public-location-internacional', i::__('Localização pública')) ?>
                            <?php $this->info('cadastro -> configuracoes-entidades -> localizacao-publica') ?>
                        </span>
                    </label>
                </div>

                <small class="field__description">
                    <?php i::_e('Marque o campo acima para tornar o endereço público ou deixe desmarcado para manter o endereço privado.') ?>
                </small>
            </div>
        </div>

        <!-- Preview + Mapa -->
        <div class="col-12">
            <p class="international-address-form__address">
                <span v-if="entity.address">{{ entity.address }}</span>
                <span v-else><?= i::_e("Sem Endereço"); ?></span>
            </p>
            <entity-map :entity="entity" editable></entity-map>
        </div>

        <?php $this->applyTemplateHook('entity-address-form-internacional', 'end'); ?>
    </div>
    <?php $this->applyTemplateHook('entity-address-form-internacional', 'after'); ?>
</div>
