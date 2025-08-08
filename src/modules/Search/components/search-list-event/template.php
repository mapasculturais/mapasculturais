<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-loading
    occurrence-card
');
?>
<div class="grid-12 search-list">
    <div class="col-3 search-list__filter">
        <div class="search-list__filter--filter">
            <search-filter-event :pseudo-query="pseudoQuery" :filter-changed="fetchOccurrences"></search-filter-event>
        </div>
    </div>
    <mc-loading :condition="loading && page == 1"></mc-loading>
    <div v-if="!loading || page > 1" class="col-9 search-list__cards">
        <div class="grid-12">
            <div v-for="occurrence in occurrences" :key="occurrence._reccurrence_string" class="col-12">
                <div v-if="newDate(occurrence)" class="search-list__cards--date">
                    <div class="search-list__cards--date-info">
                        <h2 v-if="occurrence.starts.isToday()" class="actual-date"><?= i::__('Hoje') ?><label class="month"><?= i::__('{{occurrence.starts.month()}}') ?></label></h2>
                        <h2 v-else-if="occurrence.starts.isTomorrow()" class="actual-date"><?= i::__('Amanhã') ?><label class="month"><?= i::__('{{occurrence.starts.month()}}') ?></label></h2>
                        <h2 v-else-if="occurrence.starts.isYesterday()" class="actual-date"><?= i::__('Ontem') ?><label class="month"><?= i::__('{{occurrence.starts.month()}}') ?></label></h2>
                        <template v-else>
                            <h2 class="actual-date">{{occurrence.starts.day()}}<label class="month"><?= i::__('{{occurrence.starts.month()}}') ?></label></h2>
                        </template>
                        <label class="weekend">{{occurrence.starts.weekday()}}</label>
                    </div>
                    <div class="search-list__cards--date-line"></div>
                </div>
                <occurrence-card :occurrence="occurrence"></occurrence-card>
            </div>

            <div v-if="occurrences.metadata.page < occurrences.metadata.numPages" class="col-12 load-more">
                <mc-loading :condition="loading && page > 1"></mc-loading>
                <button v-if="!loading" class="button--large button button--primary-outline" @click="loadMore()"><?php i::_e('Carregar Mais') ?></button>
            </div>
        </div>
    </div>
</div>