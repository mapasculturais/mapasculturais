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
<div class="registration-field-persons" :data-field="prop">

    <div class="registration-field-persons__list field">
        <label> {{ title }} </label>
        <small class="field__description"> {{ description }} </small>

        <div v-for="(address, index) in registration[prop]" class="registration-field-persons__person">

            <p class="semibold"> {{index + 1}}ª <?= i::__("Endereço") ?> </p>

            <div class="registration-field-persons__person-fields grid-12">
                <div class="field col-12">
                    <label>
                        <?= $this->text('nome endereço', i::__('Nome do enderço')) ?>
                    </label>
                    <input type="text" v-model="address.nome" @change="save()" :disabled="disabled" />
                </div>

                <div class="field col-12">
                    <label>
                        <?= $this->text('cep', i::__('CEP')) ?>
                    </label>
                    <input type="text" v-model="address.cep" @change="save()" @input="buscarEnderecoPorCep(address)" max-lenght="9" v-maska data-maska="#####-###" :disabled="disabled" />
                </div>

                <div class="field col-8">
                    <label>
                        <?= $this->text('logradouro', i::__('Logradouro')) ?>
                    </label>
                    <input type="text" v-model="address.logradouro" @change="save()" :disabled="disabled" />
                </div>

                <div class="field col-4">
                    <label>
                        <?= $this->text('numero', i::__('Número')) ?>
                    </label>
                    <input type="text" v-model="address.numero" @change="save()" :disabled="disabled" />
                </div>

                <div class="field col-12">
                    <label>
                        <?= $this->text('bairro', i::__('Bairro')) ?>
                    </label>
                    <input type="text" v-model="address.bairro" @change="save()" :disabled="disabled" />
                </div>

                <div class="field col-12">
                    <label>
                        <?= $this->text('complemento', i::__('complemento')) ?>
                    </label>
                    <input type="text" v-model="address.complemento" @change="save()" :disabled="disabled" />
                </div>

                <div class="field col-6" :class="[{'error' : stateError(address)}]">
                    <label>
                    <?= $this->text('estado', i::__('Estado')) ?>
                        <span v-if="required" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="address.estado" :options="states" @change-option="save()" :disabled="disabled"></mc-select>
                </div>

                <div v-if="cities(address.estado)" class="field col-6" :class="[{'error' : cityError(address)}]">
                    <label>
                    <?= $this->text('cidade', i::__('Cidade')) ?>
                        <span v-if="required" class="required">*<?= i::__('obrigatório') ?></span>
                    </label>
                    <mc-select v-model:default-value="address.cidade" :options="cities(address.estado)" @change-option="save()" :disabled="disabled"></mc-select>
                </div>
            </div>

            <div class="registration-field-persons__person-action">
                <button v-if="!disabled" type="button" class="button button--sm button--icon button--text-danger" @click="removeAddress(address)"><mc-icon name="trash"></mc-icon> <?= i::__("Remover endereço") ?></button>
            </div>
        </div>

        <div class="registration-field-persons__add-person">
            <button v-if="rules.buttonText && !disabled" type="button" class="button button--sm button--icon button--primary" @click="addNewAddress()"><mc-icon name="add"></mc-icon> {{rules.buttonText}} </button>
            <button v-if="!rules.buttonText && !disabled" type="button" class="button button--sm button--icon button--primary" @click="addNewAddress()"><mc-icon name="add"></mc-icon> <?= i::__("Adicionar novo endereço") ?> </button>
        </div>
    </div>
</div>