<div>
    <datepicker v-if="is('date')" v-model="model" :enable-time-picker="false" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

    <datepicker v-if="is('datetime')" v-model="model" :locale="locale" :weekStart="0" :format="dateFormat" :dayNames="dayNames" autoApply :id="propId" :name="prop"></datepicker>

    <datepicker v-if="is('time')" v-model="model" :locale="locale" time-picker mode-height="120" autoApply :id="propId" :name="prop"></datepicker>
</div>