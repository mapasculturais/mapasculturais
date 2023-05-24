<?php 
use MapasCulturais\i;
$this->import('
    entity-field-datepicker
')
?>
<div class="field" :class="[{error: hasErrors}, classes]" :style="checkbox ? { flexDirection: 'row' } : {}">
    <label class="field__title" v-if="!hideLabel" :for="propId">
        <slot>{{label || description.label}}</slot>
        <span v-if="description.required && !hideRequired" class="required">*<?php i::_e('obrigatório') ?></span>
    </label>
    <slot name="input" >
        <?php //@todo implementar registro de tipos de campos (#1895) ?>

        <!-- masked fields -->
        <input v-if="is('cpf')" v-maska data-maska="###.###.###-##" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" autocomplete="off">
        <input v-if="is('cnpj')" v-maska data-maska="##.###.###/####-##" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" autocomplete="off">
        <input v-if="is('brPhone')" v-maska data-maska="(##) ####0-####" data-maska-tokens="0:[0-9]:optional" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" autocomplete="off">
        <input v-if="is('cep')" v-maska data-maska="#####-###" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" autocomplete="off">

        <input v-if="is('string') || is('text')" :value="value" :id="propId" :name="prop" type="text" @input="change($event)" autocomplete="off">
    
        <textarea v-if="is('textarea')" :value="value" :id="propId" :name="prop" @input="change($event)"></textarea>

        <input v-if="is('integer') ||  is('number') ||  is('smallint')" :value="value" :id="propId" :name="prop" type="number" :min="min || description.min" :max="max || description.max" :step="description.step" @input="change($event)" autocomplete="off">

        <entity-field-datepicker v-if="is('time') || is('datetime') || is('date')" :id="propId" :entity="entity" :prop="prop" :min-date="min" :max-date="max" :field-type="fieldType" @change="change"></entity-field-datepicker>

        <input v-if="is('email') || is('url')" :value="value" :id="propId" :name="prop" :type="fieldType" @input="change($event)" autocomplete="off">
        
        <select v-if="is('select')" :value="value" :id="propId" :name="prop" @input="change($event)">
            <option v-for="optionValue in description.optionsOrder" :value="optionValue">{{description.options[optionValue]}}</option>
        </select>
        
        <template v-if="is('radio')">
            <label class="input__label input__radioLabel" v-for="optionValue in description.optionsOrder">
                <input :checked="value == optionValue" type="radio" :value="optionValue" @input="change($event)"> {{description.options[optionValue]}} 
            </label>
        </template>
        
        <template v-if="is('multiselect')">
           <div class="content">
               <label class="input__label input__checkboxLabel input__multiselect" v-for="optionValue in description.optionsOrder">
                   <input :checked="value == optionValue" type="checkbox" :value="optionValue" @input="change($event)"> {{description.options[optionValue]}} 
                </label>
            </div>
        </template>

        <template v-if="is('boolean')">
            <template v-if="checkbox">
                <label >
                    <input type="checkbox" :disabled="disabled" :checked="value" @click="change($event)" />
                    <slot name="checkboxLabel"><?= i::__("Ativo") ?></slot>
                </label>
            </template>
            <select v-else :value="value" :id="propId" :name="prop" @input="change($event)">
                <option :value='true' :selected="value"> <?= i::_e('Sim')?> </option>
                <option :value='false' :selected="!value"> <?= i::_e('Não')?>  </option>
            </select>
        </template>

    </slot>
    <small class="field__description" v-if="fieldDescription"> {{fieldDescription}} </small>
    <small class="field__error" v-if="hasErrors">        
        {{errors.join('; ')}}
    </small>
</div>