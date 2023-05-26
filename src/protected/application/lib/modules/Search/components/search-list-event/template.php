<?php

use MapasCulturais\i;

$this->import('
    entities
    mc-card
    mc-loading
    occurrence-card
');
?>

<div class="grid-12 search-list">
    <div class="col-3 search-list__filter">
        <div class="search-list__filter--filter">
            <search-filter-event :pseudo-query="pseudoQuery"></search-filter-event>
        </div>
    </div>
    <mc-loading :condition="loading"></mc-loading>
    <div v-if="!loading" class="col-9 search-list__cards">
        <div class="grid-12">
            <div  v-for="occurrence in occurrences" :key="occurrence._reccurrence_string" class="col-12">

                <div v-if="newDate(occurrence)" class="search-list__cards--date">
                    <div class="search-list__cards--date-info">
                        <h2  class="actual-date" v-if="occurrence.starts.isToday()"><?= i::__('Hoje') ?><label class="month"><?= i::__('{{occurrence.starts.month()}}')?></label></h2>
                        <h2 class="actual-date" v-else-if="occurrence.starts.isTomorrow()"><?= i::__('Amanhã') ?><label class="month"><?= i::__('{{occurrence.starts.month()}}')?></label></h2>
                        <h2 class="actual-date" v-else-if="occurrence.starts.isYesterday()"><?= i::__('Ontem') ?><label class="month"><?= i::__('{{occurrence.starts.month()}}')?></label></h2>
                        <template v-else>
                            <h2 class="actual-date" >{{occurrence.starts.day()}}<label class="month"><?= i::__('{{occurrence.starts.month()}}')?></label></h2>
                            
                        </template>
                        <label class="weekend">{{occurrence.starts.weekday()}}</label>
                    </div>
                    <div class="search-list__cards--date-line"></div>
                </div>
                <occurrence-card :occurrence="occurrence" ></occurrence-card>
            </div>
        </div>
    </div>
</div>