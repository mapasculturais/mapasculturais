<?php

use MapasCulturais\i;

$this->import('entities mapas-card occurrence-card loading');
?>

<div class="grid-12 search-list">
    <div class="col-3 search-list__filter">
        <div class="search-list__filter--filter">
            <search-filter-event :pseudo-query="pseudoQuery"></search-filter-event>
        </div>
    </div>
    <loading :condition="loading"></loading>
    <div v-if="!loading" class="col-9 search-list__cards">
        <div class="grid-12">
            <div  v-for="occurrence in occurrences" :key="occurrence._reccurrence_string" class="col-12">

                <div v-if="newDate(occurrence)" class="search-list__cards--date">
                    <div class="search-list__cards--date-info">
                        <h2 v-if="occurrence.starts.isToday()"><?= i::__('Hoje') ?></h2>
                        <h2 v-else-if="occurrence.starts.isTomorrow()"><?= i::__('Amanhã') ?></h2>
                        <h2 v-else-if="occurrence.starts.isYesterday()"><?= i::__('Ontem') ?></h2>
                        <template v-else>
                            <label class="day">{{occurrence.starts.day()}}</label>
                            <strong class="month">{{occurrence.starts.month()}}</strong>
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