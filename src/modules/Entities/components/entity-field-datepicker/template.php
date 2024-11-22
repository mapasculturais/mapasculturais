<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */
?>
<div class="entity-field-datepicker">
    <div v-if="is('date') || is('datetime')" class="entity-field-datepicker__date">
        <input v-model="dateInput" class="date-input" type="text" data-maska="##/##/####" @focus="isDateInputFocused = true" @input="onDateInput" @blur="handleBlur('date')" v-maska >
        <datepicker
            ref="datepickerCalendar"
            :teleport="true"
            v-model="modelDate" 
            @update:model-value="onDateChange" 
            :dayNames="dayNames" 
            :format="dateFormat" 
            :id="propId" 
            :locale="locale" 
            :max-date="maxDate" 
            :min-date="minDate" 
            :name="prop" 
            :weekStart="0" 
            :enable-time-picker=false
            autoApply>
            <template #trigger>
                <div class="calendar"></div>
            </template>
        </datepicker>
    </div>
        
    <div v-if="is('time') || is('datetime')" class="entity-field-datepicker__time">
        <input v-model="timeInput" class="time-input" type="text" data-maska="##:##" @focus="isTimeInputFocused = true" @blur="handleBlur('time')" v-maska>
        <datepicker
            ref="datepickerClock"
            :teleport="true"
            @update:model-value="onTimeChange" 
            v-model="modelTime"
            :format="timeFormat"
            :locale="locale"
            :id="propId" 
            :name="prop"
            mode-height="120"
            time-picker autoApply>
            <template #trigger>
                <div class="clock"></div>
            </template>
        </datepicker>
    </div>
</div>