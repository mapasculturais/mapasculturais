<div>
    <div v-if="ifDateLimitation">
        <datepicker v-if="is('date')" v-model="model" @update:model-value="change" :min-date="minDate" :max-date="maxDate" :enable-time-picker="false" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('datetime')" v-model="model" @update:model-value="change" :min-date="minDate" :max-date="maxDate" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('time')" v-model="model" @update:model-value="change" :min-date="minDate" :max-date="maxDate" :locale="locale" time-picker mode-height="120" autoApply :id="propId" :name="prop"></datepicker>
    </div>
    <div v-else>
        <datepicker v-if="is('date')" v-model="model" @update:model-value="change" :enable-time-picker="false" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('datetime')" v-model="model" @update:model-value="change" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('time')" v-model="model" @update:model-value="change" :locale="locale" time-picker mode-height="120" autoApply :id="propId" :name="prop"></datepicker>
    </div>
</div>