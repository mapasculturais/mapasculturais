<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-entities
    mc-icon
    mc-link
    occurrence-card
    search-filter-event
    search-map
');
?>
<search-map type="space" endpoint="findByEvents" :pseudo-query="pseudoQuery" :entityRawProcessor="spaceRawProcessor" @open-popup="open($event)" @close-popup="close($event)">
    <template #filter>
        <search-filter-event :pseudo-query="pseudoQuery" position="map"></search-filter-event>
    </template>
</search-map>

<div v-if="space" class="search-map__events">
    <a class="search-map__events--close button button--icon" @click="close()"><?= i::__('Fechar');?> <mc-icon name="close"></mc-icon></a>
    
    <div class="search-map__events--spaces">
        <label class="search-map__events--spaces-title"><?= i::__('Eventos encontrados no espaço:') ?></label>

        <div class="space-link">
            <div class="space-link__icon">
                <mc-icon name="space"></mc-icon>
            </div>
            <mc-link :entity="space"></mc-link>
        </div>
        <p class="search-map__events--adress" v-if="space.endereco">
            <mc-icon name="map-pin"></mc-icon>
            <label class="search-map__events--adress-label">{{space.endereco}}</label>
        </p>

    </div>
    <mc-entities type="event" endpoint="occurrences" :raw-processor="occurrenceRawProcessor" :query="spaceQuery" watch-query>
        <template #default="{entities}">
            <template v-for="occurrence in entities" :key="occurrence._reccurrence_string">
                <div class="search-map__card">
                    <div v-if="newDate(occurrence)" class="search-map__cards--date">
                        <div class="search-map__cards--date-info">
                            <h2 v-if="occurrence.starts.isToday()"><?= i::__('Hoje') ?></h2>
                            <h2 v-else-if="occurrence.starts.isTomorrow()"><?= i::__('Amanhã') ?></h2>
                            <h2 v-else-if="occurrence.starts.isYesterday()"><?= i::__('Ontem') ?></h2>
                            <template v-else>
                                <label class="day">{{occurrence.starts.day()}}</label>
                                <strong class="month">{{occurrence.starts.month()}}</strong>
                            </template>
                            <label class="weekend">{{occurrence.starts.weekday()}}</label>
                        </div>
                        <div class="search-map__cards--date-line"></div>
                    </div>
                </div>
                <occurrence-card :occurrence="occurrence" hide-space></occurrence-card>
            </template>
        </template>
        <template #loading>
            <div>
                <mc-icon name="loading"></mc-icon>
            </div>
        </template>
    </mc-entities>
</div>