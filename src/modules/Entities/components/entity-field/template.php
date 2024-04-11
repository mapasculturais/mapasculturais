<?php 
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    entity-field-datepicker
    mc-alert
    mc-currency-input
')
?>
<div v-if="propExists()" class="field" :class="[{error: hasErrors}, classes]">
    <label class="field__title" v-if="!hideLabel && !is('checkbox')" :for="propId">
        <slot>{{label || description.label}}</slot>
        <span v-if="description.required && !hideRequired" class="required">*<?php i::_e('obrigatório') ?></span>
    </label>
    <slot name="input" >
        <?php //@todo implementar registro de tipos de campos (#1895) ?>

        <!-- masked fields -->
        <input v-if="is('cpf')" v-maska data-maska="###.###.###-##" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off">
        <input v-if="is('cnpj')" v-maska data-maska="##.###.###/####-##" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off">
        <input v-if="is('brPhone')" v-maska data-maska="(##) ####0-####" data-maska-tokens="0:[0-9]:optional" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off">
        <input v-if="is('cep')" v-maska data-maska="#####-###" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off">

        <input v-if="is('string') || is('text')" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder">
        <div  v-if="is('textarea') && prop=='shortDescription'" class="field__shortdescription">
            <textarea :id="propId" :value="value" :name="prop" @input="change($event)" @blur="change($event,true)" :maxlength="400"></textarea>
                <p>
                {{ value ? value?.length : '0' }}/400
                </p>
        </div>
        <textarea v-if="is('textarea') && !prop=='shortDescription'" :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)"></textarea>

        <textarea v-if="is('textarea') && prop=='longDescription'" :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)" class="field__longdescription"></textarea>

        <input v-if="is('integer') ||  is('number') ||  is('smallint')" :value="value" :id="propId" :name="prop" type="number" :min="min || description.min" :max="max || description.max" :step="description.step" @input="change($event)" @blur="change($event,true)" autocomplete="off">

        <entity-field-datepicker v-if="is('time') || is('datetime') || is('date')" :id="propId" :entity="entity" :prop="prop" :min-date="min" :max-date="max" :field-type="fieldType" @change="change"></entity-field-datepicker>

        <input v-if="is('email') || is('url')" :value="value" :id="propId" :name="prop" :type="fieldType" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder">
    
        <input v-if="is('socialMedia')" :value="value" :id="propId" :name="prop" :type="fieldType" @input="change($event)" @blur="change($event,true)" autocomplete="off" :placeholder="placeholder || description?.placeholder">
        
        <select v-if="is('select')" :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)">
            <option v-for="optionValue in description.optionsOrder" :value="optionValue">{{description.options[optionValue]}}</option>
        </select>
        
        <template v-if="is('radio')">
            <label class="input__label input__radioLabel" v-for="optionValue in description.optionsOrder">
                <input :checked="value == optionValue" type="radio" :value="optionValue" @input="change($event)" @blur="change($event,true)"> {{description.options[optionValue]}} 
            </label>
        </template>
        
        <template v-if="is('multiselect')">
           <div class="field__group">
               <label class="input__label input__checkboxLabel input__multiselect" v-for="optionValue in description.optionsOrder">
                   <input :checked="value?.includes(optionValue)" type="checkbox" :value="optionValue" @change="change($event)"> {{description.options[optionValue]}} 
                </label>
            </div>
        </template>

        <template v-if="is('checkbox')">
            <div class="field__group">
                <label class="field__checkbox">
                    <input :id="propId" type="checkbox" :disabled="disabled" :checked="value" @click="change($event)" />
                    <slot>{{label || description.label}}</slot>
                </label>
            </div>
        </template>

        <template v-if="is('boolean')">
            <select :value="value" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)">
                <option :value='true' :selected="value"> <?= i::_e('Sim')?> </option>
                <option :value='false' :selected="!value"> <?= i::_e('Não')?>  </option>
            </select>
        </template>
        
        <template v-if="is('currency')">
            <mc-currency-input v-model="currencyValue" :entity="entity" :id="propId" :name="prop" @input="change($event)" @blur="change($event,true)"></mc-currency-input>
        </template>

    </slot>
    <small class="field__description" v-if="!hideDescription && (fieldDescription || description.description)"> {{ fieldDescription || description.description}} </small>
    <small class="field__error" v-if="hasErrors">        
        {{errors.join('; ')}}
    </small>
</div>