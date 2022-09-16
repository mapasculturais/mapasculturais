<?php
use MapasCulturais\i;
$this->import('search-filter mc-multiselect mc-tag-list mc-icon');
?>

<search-filter :position="position" :pseudo-query="pseudoQuery">
    <form class="form" @submit="$event.preventDefault()">
        <label class="form__label">
            <?= i::_e('Filtros de eventos') ?>
        </label>
        <div class="field">
            <label> <?php i::_e('Eventos acontecendo') ?></label>
            <div class="datepicker">
                <datepicker 
                    :locale="locale" 
                    :weekStart="0"
                    v-model="date" 
                    :enableTimePicker='false' 
                    :format="dateFormat"
                    :presetRanges="presetRanges" 
                    :dayNames="['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab']"
                    range multiCalendars multiCalendarsSolo autoApply utc></datepicker>
    
                <button @click="prevInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-left"></mc-icon> </button>
                <button @click="nextInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-right"></mc-icon> </button>
            </div>
        </div>

        <div class="field">
            <label> <?php i::_e('Status do evento') ?></label>
            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Eventos oficiais') ?> </label>
        </div>
        <div class="field">
            <label> <?php i::_e('Classificação Etária') ?></label>
            <mc-multiselect :model="pseudoQuery['event:classificacaoEtaria']" :items="ageRating" #default="{popover}" hide-filter hide-button>
                <input class="mc-multiselect--input" v-model="pseudoQuery['event:classificacaoEtaria'].filter" @focus="popover.open()" placeholder="<?= i::esc_attr__('Selecione') ?>">
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['event:classificacaoEtaria']" classes="event__background event__color"></mc-tag-list>
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