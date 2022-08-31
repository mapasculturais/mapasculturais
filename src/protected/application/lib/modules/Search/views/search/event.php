<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search search-map search-list-events search-filter-event');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Eventos'), 'url' => $app->createUrl('events')],
];

?>
<search page-title="<?php i::esc_attr_e('Eventos') ?>" entity-type="event" :initial-pseudo-query="{'term:linguagem':[]}">

    <template #create-button>
        
    </template>

    <template #default="{pseudoQuery}">
        <tabs class="search__tabs">
            <template  #before-tablist>
                <label class="search__tabs--before">
                    Visualizar como:
                </label> 
            </template>
            
            <tab icon="list" label="Lista" slug="list">
                <div class="search__tabs--list">
                    <search-list-events :pseudo-query="pseudoQuery"></search-list-events>
                </div>
            </tab>
        
            <tab icon="map" label="Mapa" slug="map">
                <div class="search__tabs--map">
                    <search-map type="event" :pseudo-query="pseudoQuery">
                        <template #filter>
                            <search-filter-event :pseudo-query="pseudoQuery" position="map"></search-filter-event>
                        </template>
                    </search-map>

                </div>
            </tab>
        </tabs>
    </template>
</search>