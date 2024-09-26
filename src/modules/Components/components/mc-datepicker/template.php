<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

?>

<div class="mc-datepicker" :class="{ 'mc-datepicker__datetime': fieldType === 'datetime'}">
    <div class="mc-datepicker__date" v-if="isDateType">
        <input
            v-model="dateInput"
            class="date-input"
            type="text"
            data-maska="##/##/####"
            @focus="isDateInputFocused = true"
            @blur="handleBlur('date')"
            v-maska>
        <datepicker
            ref="datepickerCalendar"
            :teleport="true"
            v-model="modelDate"
            @update:model-value="onDateChange"
            :dayNames="dayNames"
            :format="dateFormat"
            :id="propId"
            :locale="locale"
            :name="propId"
            :weekStart="0"
            :enable-time-picker="false"
            autoApply>
            <template #trigger>
                <div class="calendar"></div>
            </template>
        </datepicker>
    </div>

    <div class="mc-datepicker__time" v-if="isTimeType">
        <input
            v-model="timeInput"
            class="time-input"
            type="text"
            data-maska="##:##"
            @focus="isTimeInputFocused = true"
            @blur="handleBlur('time')"
            v-maska>
        <datepicker
            ref="datepickerClock"
            :teleport="true"
            v-model="modelTime"
            @update:model-value="onTimeChange"
            :format="timeFormat"
            :locale="locale"
            :id="propId"
            :name="propId"
            mode-height="120"
            time-picker
            autoApply>
            <template #trigger>
                <div class="clock"></div>
            </template>
        </datepicker>
    </div>
</div>