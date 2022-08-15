<?php 
use MapasCulturais\i; 
?>
<div class="field" :class="{error: hasErrors}">
    <label v-if="showLabel" :for="propId">{{label || description.label}} <span v-if="description.required" class="required">*<?php i::_e('obrigatório') ?></span></label> 
    
    <slot  name="input" >
        <?php //@todo implementar registro de tipos de campos (#1895) ?>
        <input  v-if="is('string')" :value="value" :id="propId" :name="prop" type="text"  @change="change($event)">

        <textarea v-if="is('text')" :value="value" :id="propId" :name="prop" @change="change($event)"></textarea>

        <input v-if="is('date') || is('number')" :value="value" :id="propId" :name="prop" :type="fieldType" :min="description.min" :max="description.max" :step="description.step" @change="change($event)">

        <input v-if="is('email') || is('url')" :value="value" :id="propId" :name="prop" :type="fieldType" @change="change($event)">
        
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
    </slot>
    <small class="field__error" v-if="hasErrors">
  
        {{errors.join('; ')}}
    </small>
</div>