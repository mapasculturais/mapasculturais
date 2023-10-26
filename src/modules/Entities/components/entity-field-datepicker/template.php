<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div>
    <datepicker 
        v-if="is('date')" 
        v-model="model" 
        @update:model-value="change" 
        :dayNames="dayNames" 
        :format="dateFormat" 
        :id="propId" 
        :locale="locale" 
        :max-date="maxDate" 
        :min-date="minDate" 
        :name="prop" 
        :text-input-options="dateTextInputOptions" 
        :weekStart="0" 
        :enable-time-picker=false
        text-input autoApply>
        <template #dp-input="{ value, onBlur, onInput, onEnter, onTab, onClear }">
            <input type="text" data-maska="##/##/####" :value="value" maxlength="10" @input="onChange($event, onInput)" @blur="onBlur" @keydown.enter="onEnter" @keydown.tab="onTab" v-maska >
        </template>
    </datepicker>

    <datepicker 
        v-if="is('datetime')" 
        v-model="model" 
        @update:model-value="change" 
        :dayNames="dayNames" 
        :format="datetimeFormat" 
        :id="propId" 
        :locale="locale" 
        :max-date="maxDate" 
        :min-date="minDate" 
        :name="prop" 
        :text-input-options="datetimeTextInputOptions" 
        :weekStart="0" 
        text-input autoApply>
        <template #dp-input="{ value, onBlur, onInput, onEnter, onTab, onClear }">
            <input type="text" data-maska="##/##/#### ##:##" :value="value" maxlength="16" @input="onChange($event, onInput)" @blur="onBlur" @keydown.enter="onEnter" @keydown.tab="onTab" v-maska >
        </template>
    </datepicker>

    <datepicker 
        v-if="is('time')" 
        @update:model-value="change" 
        :locale="locale"
        :id="propId" 
        :name="prop"
        mode-height="120"
        :text-input-options="timeTextInputOptions" 
        text-input time-picker autoApply>
        <template #dp-input="{ value, onInput, onEnter, onTab, onClear }">
            <input type="text" data-maska="##:##" :value="value" maxlength="10" @input="onChange($event, onInput)" @keydown.enter="onEnter" @keydown.tab="onTab" v-maska >
        </template>
    </datepicker>
</div>