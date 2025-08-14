<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field-bank-info
    entity-field-datepicker
    entity-field-links
    entity-field-location
    entity-field-location-global
    entity-field-seals
    entity-file
    mc-alert
    mc-currency-input
    mc-multiselect
    mc-tag-list
    select-municipio
')
?>
<div v-if="propExists()" class="field" :class="[{error: hasErrors}, {disabled: readonly || disabled}, classes]" :data-field="prop">
    <label class="field__title" v-if="!hideLabel && !is('checkbox')" :for="propId">
        <slot>{{label || description.label}}</slot>
        <span v-if="description.required && !hideRequired" class="required">*<?php i::_e('obrigatório') ?></span>
        <slot name="info"></slot>
    </label>

    <small class="field__description" v-if="descriptionFirst && (!hideDescription && (fieldDescription || description.description))"> {{ fieldDescription || description.description}} </small>

    <div class="field__input">
        <slot name="input">
            <?php //@todo implementar registro de tipos de campos (#1895) ?>

            <input v-if="is('cpf')" v-maska data-maska="###.###.###-##" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">
            <input v-else-if="is('cnhNumero')" v-maska data-maska="###########" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">
            <input v-else-if="is('rgNumero')" v-maska data-maska="########" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">
            
            <input v-else-if="is('cnpj')" v-maska data-maska="##.###.###/####-##" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">

            <input v-else-if="is('brPhone')" v-maska data-maska="['(##) #####-####','(##) ####-####']" data-maska-tokens="0:[0-9]:optional" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">
            <input v-else-if="is('cep')" v-maska data-maska="#####-###" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">
            <input v-else-if="is('fieldMask')" v-maska :data-maska="mask" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">

            <input v-else-if="is('string') || is('text')" :value="value" :id="propId" :name="prop" type="text" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder" :disabled="readonly || disabled" :readonly="readonly">
            
            <input v-else-if="is('integer') ||  is('number') ||  is('smallint')" :value="value" :id="propId" :name="prop" type="number" :min="min || description.min" :max="max || description.max" :step="description.step" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled" :readonly="readonly">
            
            <input v-else-if="is('email') || is('url')" :value="value" :id="propId" :name="prop" :type="fieldType" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder" :disabled="readonly || disabled" :readonly="readonly">
            
            <input v-else-if="is('socialMedia')" :value="value" :id="propId" :name="prop" :type="fieldType" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder" :disabled="readonly || disabled" :readonly="readonly">
            
            <entity-field-datepicker v-else-if="is('time') || is('datetime') || is('date')" :id="propId" :entity="entity" :prop="prop" :min-date="min" :max-date="max" :field-type="fieldType" @change="change($event, true)"></entity-field-datepicker>
            
            <textarea ref="textarea" v-else-if="is('textarea')" :value="value" :id="propId" :name="prop" :maxLength="maxLength" @input="change($event)" @blur="change($event,true)" :disabled="readonly || disabled" :readonly="readonly"></textarea>


            <template v-else-if="is('file')">
                <entity-file :entity="entity" disableName :titleModal="titleModal" :groupName="groupName" classes="col-12" editable button-text-value="Anexar arquivo" :disabled="readonly || disabled" :readonly="readonly"></entity-file>
            </template>


            <template v-else-if="is('select')">
                <template v-if="description.registrationFieldConfiguration?.config?.viewMode === 'radio'">
                    <label class="input__label input__radioLabel" v-for="(optionLabel, optionValue) in description.options">
                        <input :checked="isRadioChecked(value, optionValue)" type="radio" :value="optionValue" @input="change($event,true)" @blur="change($event)" :disabled="readonly || disabled"> {{description.options[optionValue]}}
                    </label>
                </template>

                <select v-else :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)" :disabled="readonly || disabled">
                    <option v-for="optionValue in description.optionsOrder" :value="optionValue">{{description.options[optionValue]}}</option>
                </select>
            </template>

            <template v-else-if="is('radio')">
                <div class="field__group">
                    <label class="input__label input__radioLabel" v-for="(optionLabel, optionValue) in description.options">
                        <input :checked="isRadioChecked(value, optionValue)" type="radio" :value="optionValue" @input="change($event,true)" @blur="change($event)" :disabled="readonly || disabled"> {{description.options[optionValue]}}
                    </label>
                </div>
            </template>

            <template v-else-if="is('links')">
                <entity-field-links :entity="entity" :prop="prop" :show-title="Boolean(description?.registrationFieldConfiguration?.config?.title)" @change="change($event, true)" :editable="!disabled"></entity-field-links>
            </template>

            <template v-else-if="is('multiselect') || is('checklist')">
            <div class="field__group">
                    <template v-if="isMultiSelect()">
                        <mc-multiselect :placeholder="placeholder || description?.placeholder" @selected="change($event)" :model="selectedOptions[prop]" :items="description.options" #default="{popover,setFilter}" :max-options="maxOptions" :preserve-order="preserveOrder" hide-filter hide-button></mc-multiselect>

                        <mc-tag-list :tags="selectedOptions[prop]" :labels="description?.options" classes="opportunity__background" @remove="change($event)" editable></mc-tag-list>
                    </template>

                    <template v-else>
                        <div v-if="maxOptions && maxOptions > 0">
                            <label>
                                <?php i::_e('Você selecionou') ?>
                                {{ value?.length || 0 }}/{{ maxOptions }}
                                <?php i::_e('opções') ?>
                            </label>
                        </div>

                        <label class="input__label input__checkboxLabel input__multiselect" v-for="optionValue in description.optionsOrder">
                        <input :checked="value?.length > 0 && value?.includes(optionValue)" type="checkbox" :value="optionValue" @change="change($event)" :disabled="readonly || disabled || (maxOptions && value?.length >= maxOptions && !value?.includes(optionValue))" /> {{description.options[optionValue]}}
                        </label>
                    </template>
                </div>
            </template>

            <template v-else-if="is('checkbox')">
                <div class="field__group">
                    <label class="field__checkbox">
                        <input :id="propId" type="checkbox" :checked="value" @click="change($event)"  :disabled="readonly || disabled" />
                        <span>
                            <slot>{{label || description.label}}</slot>
                            <slot name="info"></slot>
                        </span>
                    </label>
                </div>
            </template>

            <template v-else-if="is('boolean')">
                <select :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)" :disabled="readonly || disabled">
                    <option :value='true' :selected="value"> <?= i::_e('Sim')?> </option>
                    <option :value='false' :selected="!value"> <?= i::_e('Não')?>  </option>
                </select>
            </template>

            <template v-else-if="is('currency')">
                <mc-currency-input v-model="currencyValue" :entity="entity" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)"></mc-currency-input>
            </template>

            <template v-else-if="is('color')">
                <div class="field__color">
                    <div class="field__color-input">
                        <input :value="value" :id="propId" :name="prop" type="color" @input="change($event)" @blur="change($event,true)" autocomplete="off" :disabled="readonly || disabled"/>
                    </div>
                </div>
            </template>

            <template v-else-if="is('location')">
                <entity-field-location-global :entity="entity" :field-name="prop" :configs="description?.registrationFieldConfiguration"></entity-field-location-global>
                <!-- <entity-field-location :entity="entity" :field-name="prop" :configs="description?.registrationFieldConfiguration?.config"></entity-field-location> -->
            </template>

            <template v-else-if="is('bankFields')">
                <entity-field-bank-info @change="change($event, true)"  :field-name="prop" :entity="entity"></entity-field-bank-info>
            </template>

            <template v-else-if="is('municipio')">
                <select-municipio :entity="entity" :prop="prop" @change="change($event)"></select-municipio>
            </template>

            <div v-if="maxLength && (is('string') || is('text') || is('textarea'))" class="field__length">{{ value ? value?.length : '0' }}/{{maxLength}}</div>
        </slot>

        <entity-field-seals :entity="entity" :prop="prop"></entity-field-seals>
    </div>

    <small class="field__description" v-if="!descriptionFirst && (!hideDescription && (fieldDescription || description.description))"> {{ fieldDescription || description.description}} </small>

    <small class="field__error" v-if="hasErrors">
        {{errors.join('; ')}}
    </small>
</div>
