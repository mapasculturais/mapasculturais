<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
    mc-multiselect 
    mc-tag-list
    search-filter 
');
?>
<search-filter :position="position" :pseudo-query="pseudoQuery">
    <label class="form__label">
        <?= i::_e('Filtros de eventos') ?>
    </label>
    <form class="form" @submit="$event.preventDefault()">
        <?php $this->applyTemplateHook('search-filter-event', 'begin') ?>
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
                <div class="filter-btn">
                    <button @click="prevInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-left"></mc-icon> </button>
                    <button @click="nextInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-right"></mc-icon> </button>
                </div>
            </div>
        </div>
        <div class="field">
            <label> <?php i::_e('Status do evento') ?></label>
            <label class="verified"><input v-model="pseudoQuery['event:@verified']" type="checkbox"> <?php i::_e('Eventos oficiais') ?> </label>
        </div>
        <div class="field">
            <label> <?php i::_e('Classificação Etária') ?></label>
            <mc-multiselect :model="pseudoQuery['event:classificacaoEtaria']" title="<?php i::_e('Classificação Etária')?>" :items="ageRating" hide-filter hide-button>
                <template #default="{popover, setFilter}">
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" :triggers="['click']" placeholder="<?= i::esc_attr__('Selecione') ?>">
                </template>
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['event:classificacaoEtaria']" classes="event__background event__color"></mc-tag-list>
        </div>
        <div class="field">
            <label> <?php i::_e('Linguagens') ?></label>
            <mc-multiselect :model="pseudoQuery['event:term:linguagem']" :items="terms" title="<?php i::_e('Selecione as linguagens') ?>" hide-filter hide-button>
                <template #default="{popover, setFilter}">
                    <input class="mc-multiselect--input" @keyup="setFilter($event.target.value)" @focus="popover.open()" :triggers="['click']" placeholder="<?= i::esc_attr__('Selecione as linguagens') ?>">
                </template>
            </mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['event:term:linguagem']" classes="event__background event__color"></mc-tag-list>
        </div>
        <?php $this->applyTemplateHook('search-filter-event', 'end') ?>
    </form>
    <a class="clear-filter" @click="clearFilters()"><?php i::_e('Limpar todos os filtros') ?></a>
</search-filter>