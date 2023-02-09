<div>
    <div v-if="isMinDate">
        <datepicker v-if="is('date')" v-model="model" :min-date="minDate" :max-date="maxDate" :enable-time-picker="false" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('datetime')" v-model="model" :min-date="minDate" :max-date="maxDate" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('time')" v-model="model" :min-date="minDate" :max-date="maxDate" :locale="locale" time-picker mode-height="120" autoApply :id="propId" :name="prop"></datepicker>
    </div>
    <div v-else>
        <datepicker v-if="is('date')" v-model="model" :enable-time-picker="false" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('datetime')" v-model="model" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

        <datepicker v-if="is('time')" v-model="model" :locale="locale" time-picker mode-height="120" autoApply :id="propId" :name="prop"></datepicker>
    </div>
</div>