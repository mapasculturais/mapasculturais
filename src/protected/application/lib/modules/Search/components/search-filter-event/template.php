<?php
use MapasCulturais\i;
$this->import('search-filter mc-multiselect mc-tag-list mc-icon');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de eventos') ?>
        </label>
        <div>
            <datepicker 
                :weekStart="0"
                v-model="date" 
                locale="pt-BR" 
                :enableTimePicker='false' 
                :format="dateFormat"
                cancelText="<?= i::esc_attr__('Cancelar') ?>" 
                selectText="<?= i::esc_attr__('Ok') ?>"
                :presetRanges="presetRanges" 
                range multiCalendars multiCalendarsSolo autoApply utc></datepicker>

            <button @click="prevInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-left"></mc-icon> </button>
            <button @click="nextInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-right"></mc-icon> </button>
        </div>

        <div class="field">
            <label><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Eventos oficiais') ?> </label>
        </div>
        <div class="field">
            <label> <?php i::_e('Linguagens') ?></label>
            <mc-multiselect :model="pseudoQuery['event:term:linguagem']" :items="terms" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['event:term:linguagem'].filter" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione as linguagens') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['event:term:linguagem']" classes="event__background event__color"></mc-tag-list>
        </div>
    </form>
</search-filter>