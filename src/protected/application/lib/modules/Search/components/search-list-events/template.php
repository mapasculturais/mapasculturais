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
    <div v-if="!loading" class="col-9" v-for="occurrence in occurrences" :key="occurrence._reccurrence_string">
        <div v-if="newDate(occurrence)">
            <h2 v-if="occurrence.starts.isToday()"><?= i::__('Hoje') ?></h2>
            <h2 v-else-if="occurrence.starts.isTomorrow()"><?= i::__('Amanhã') ?></h2>
            <h2 v-else-if="occurrence.starts.isYesterday()"><?= i::__('Ontem') ?></h2>
            <template v-else>
                <h2>{{occurrence.starts.day()}}</h2>
                <strong>{{occurrence.starts.month()}}</strong>
            </template>
            <h3>{{occurrence.starts.weekday()}}</h3>
        </div>
        <occurrence-card :occurrence="occurrence"></occurrence-card> 
    </div>
</div>