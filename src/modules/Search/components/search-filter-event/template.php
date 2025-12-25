<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$queryParams =  [
    '@order' => 'id ASC',
    '@select' => 'id,name,files.avatar',
];
$querySeals = new MapasCulturais\ApiQuery(MapasCulturais\Entities\Seal::class, $queryParams);

$this->jsObject['config']['event_filters'] = [
    'seals' => $querySeals->getFindResult(),
];

$this->import('
    mc-icon
    mc-currency-input
    mc-multiselect
    mc-tag-list
    search-filter
');
?>
<search-filter :position="position" :pseudo-query="pseudoQuery">
    <div style="display: flex; align-items: stretch;">
        <mc-icon name="filter" style="color: #2B74D9; font-size: 1.1rem;"></mc-icon>
        <label class="form__label" style="margin-left: 10px; color: #2B74D9; line-height: 1.1rem;">
            <?= i::_e('Filtros') ?>
        </label>
    </div>
    <form class="form" @submit="$event.preventDefault()" style="max-height: none !important;">
        <?php $this->applyTemplateHook('search-filter-event', 'begin') ?>
        <div class="field">
            <label> <?php i::_e('Período') ?></label>
            <div class="datepicker">
                <datepicker
                    :teleport="true"
                    :locale="locale"
                    :weekStart="0"
                    v-model="date"
                    :enableTimePicker='false'
                    :format="dateFormat"
                    :presetRanges="presetRanges"
                    :dayNames="['Dom', 'Seg', 'Ter', 'Qua', 'Qui', 'Sex', 'Sab']"
                    range multiCalendars multiCalendarsSolo autoApply teleport-center utc></datepicker>
                <div class="filter-btn">
                    <button @click="prevInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-left"></mc-icon> </button>
                    <button @click="nextInterval()" class="button button--rounded button--outline"> <mc-icon name="arrow-right"></mc-icon> </button>
                </div>
            </div>
        </div>
        <div class="field">
            <label class="verified"><input v-model="pseudoQuery['@verified']" type="checkbox"> <?php i::_e('Eventos oficiais') ?> </label>
        </div>

        <div class="field">
            <label><?= i::__('Estado') ?></label>
            <mc-multiselect
                :model="pseudoQuery['En_Estado']"
                :items="states"
                @change="filterByState"
                title="<?php i::_e('Estado') ?>"
                placeholder="<?= i::esc_attr__('Todos') ?>"
                hide-filter
                hide-button></mc-multiselect>
            <mc-tag-list
                editable
                :tags="pseudoQuery['En_Estado']"
                :labels="states"
                classes="agent__background agent__color"></mc-tag-list>
        </div>

        <div class="field">
            <label><?= i::__('Municípios') ?></label>
            <mc-multiselect
                :model="pseudoQuery['En_Municipio']"
                :items="cities"
                @change="filterByCities"
                title="<?php i::_e('Municípios') ?>"
                placeholder="<?= i::esc_attr__('Todos') ?>"
                :disabled="!pseudoQuery['En_Estado']?.length"
                hide-filter
                hide-button></mc-multiselect>
            <mc-tag-list
                editable
                :tags="pseudoQuery['En_Municipio']"
                :labels="cities"
                classes="agent__background agent__color"></mc-tag-list>
        </div>

        <div class="field">
            <label> <?php i::_e('Selos') ?></label>
            <mc-multiselect class="col-3 sm:col-4" :model="pseudoQuery['@seals']" :items="sealsNames" placeholder="<?= i::esc_attr__('Todos') ?>" :hide-filter="hideFilters" hide-button></mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['@seals']" :labels="sealsLabels" classes="event__background event__color"></mc-tag-list>
        </div>

        <div class="field">
            <label> <?php i::_e('Linguagens') ?></label>
            <mc-multiselect :model="pseudoQuery['event:term:linguagem']" placeholder="<?= i::esc_attr__('Todas') ?>" :items="terms" title="<?php i::_e('Selecione as linguagens') ?>" hide-filter hide-button></mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['event:term:linguagem']" classes="event__background event__color"></mc-tag-list>
        </div>

        <div class="field">
            <label> <?php i::_e('Classificação Etária') ?></label>
            <mc-multiselect :model="pseudoQuery['event:classificacaoEtaria']" placeholder="<?= i::esc_attr__('Todas') ?>" title="<?php i::_e('Classificação Etária') ?>" :items="ageRating" hide-filter hide-button></mc-multiselect>
            <mc-tag-list editable :tags="pseudoQuery['event:classificacaoEtaria']" classes="event__background event__color"></mc-tag-list>
        </div>

        <div class="field">
            <label> <?php i::_e('Nome do evento') ?></label>
            <input id="name" type="text" v-model="pseudoQuery['event:name']" placeholder="<?= i::esc_attr__('Digite') ?>" />
        </div>

        <?php $this->applyTemplateHook('search-filter-event', 'end') ?>
    </form>
    <a class="clear-filter" @click="clearFilters()"><?php i::_e('limpar todos os filtros') ?></a>
</search-filter>