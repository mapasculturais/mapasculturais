<label v-if="showLabel" :for="propId">{{description.label}}</label>
<small v-if="hasErrors()">
    {{getErrors().join('; ')}}
</small>
<template v-if="!hasSlot('input')">
    <?php //@todo implementar registro de tipos de campos (#1895) ?>
    <input v-if="is('string')" v-model="value" :id="propId" :name="prop" type="text" @change="change()">

    <textarea v-if="is('text')" v-model="value" :id="propId" :name="prop" @change="change()"></textarea>

    <input v-if="is('date') || is('number')" v-model="value" :id="propId" :name="prop" :type="type" :min="description.min" :max="description.max" :step="description.step" @change="change()">

    <input v-if="is('email') || is('url')" v-model="value" :id="propId" :name="prop" :type="type" @change="change()">
    
    <select v-if="is('select')" v-model="value" :id="propId" :name="prop" @change="change()">
        <option v-for="optionValue in description.optionsOrder" :value="optionValue">{{description.options[value]}}</option>
    </select>
    
    <template v-if="is('radio')">
        <label v-for="optionValue in description.optionsOrder">
            <input v-model="value" type="radio" :value="optionValue" @change="change()"> {{description.options[optionValue]}} 
        </label>
    </template>
    
    <template v-if="is('multiselect')">
        <label v-for="optionValue in description.optionsOrder">
            <input v-model="value" type="checkbox" :value="optionValue" @change="change()"> {{description.options[optionValue]}} 
        </label>
    </template>
</template>
<slot v-if="hasSlot('input')" name="input"></slot>