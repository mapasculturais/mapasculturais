<?php
use MapasCulturais\i;

$this->import('
    entities
    mc-link
    mc-icon
    occurrence-card
    search-filter-event
    search-map
');
?>
<search-map 
    type="space" 
    endpoint="findByEvents" 
    :pseudo-query="pseudoQuery" 
    :entityRawProcessor="spaceRawProcessor" 
    @open-popup="open($event)"
    @close-popup="close($event)">
    <template #filter>
        <search-filter-event :pseudo-query="pseudoQuery" position="map"></search-filter-event>
    </template>
</search-map>

<div v-if="space" style="max-height:500px; min-height:400px; overflow-y:auto; position:absolute; width:300px; background-color: darkolivegreen; top:50px; left:100px; z-index:1000">
    <?= i::__('Eventos encontrados no espaço:') ?>
    <h1><mc-link :entity="space" icon></mc-link></h1>
    <p v-if="space.endereco">
        <?= i::__('Onde: ') ?>{{space.endereco}}
    </p>
    <entities 
        type="event" 
        endpoint="occurrences" 
        :raw-processor="occurrenceRawProcessor" 
        :query="spaceQuery"
        watch-query>
        <template #default="{entities}">
            <template v-for="occurrence in entities" :key="occurrence._reccurrence_string" >
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
                <occurrence-card :occurrence="occurrence" hide-space></occurrence-card>
            </template>
        </template>
        <template #loading>
            <div style="font-size: 55px;"><mc-icon name="loading"></mc-icon></div>
        </template>
    </entities>
</div>