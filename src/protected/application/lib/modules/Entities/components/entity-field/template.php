<?php 
use MapasCulturais\i;
$this->import('
    mc-input-mask-wrapper
    mc-input-datepicker-wrapper
')
?>
<div class="field" :class="[{error: hasErrors}, classes]">
    <label v-if="!hideLabel" :for="propId">
        <slot>{{label || description.label}}</slot>
        <span v-if="description.required && !hideRequired" class="required">*<?php i::_e('obrigatório') ?></span>
    </label>
    <slot name="input" >
        <?php //@todo implementar registro de tipos de campos (#1895) ?>

        <mc-input-mask-wrapper v-if="is('string') && mask" :entity="entity" :prop="prop"></mc-input-mask-wrapper>

        <input  v-if="is('string') && !mask" :value="value" :id="propId" :name="prop" type="text" @change="change($event)">

        <textarea v-if="is('text')" :value="value" :id="propId" :name="prop" @change="change($event)"></textarea>

        <input v-if="is('integer') ||  is('number') ||  is('smallint')" :value="value" :id="propId" :name="prop" type="number" :min="min || description.min" :max="max || description.max" :step="description.step" @change="change($event)">

        <mc-input-datepicker-wrapper v-if="is('time') || is('datetime') || is('date')" :id="propId" :entity="entity" :prop="prop" :min-date="minDate" :max-date="maxDate" :field-type="fieldType"></mc-input-datepicker-wrapper>

        <input v-if="is('email') || is('url')" :value="value.format" :id="propId" :name="prop" :type="fieldType" @change="change($event)">
        
        <select v-if="is('select')" :value="value" :id="propId" :name="prop" @change="change($event)">
            <option v-for="optionValue in description.optionsOrder" :value="optionValue">{{description.options[optionValue]}}</option>
        </select>
        
        <template v-if="is('radio')">
            <label v-for="optionValue in description.optionsOrder">
                <input :checked="value == optionValue" type="radio" :value="optionValue" @change="change($event)"> {{description.options[optionValue]}} 
            </label>
        </template>
        
        <template v-if="is('multiselect')">
            <label v-for="optionValue in description.optionsOrder">
                <input :checked="value == optionValue" type="checkbox" :value="optionValue" @change="change($event)"> {{description.options[optionValue]}} 
            </label>
        </template>

        <template v-if="is('boolean')">
            <select :value="value" :id="propId" :name="prop" @change="change($event)">
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