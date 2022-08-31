<?php 
use MapasCulturais\i;
 
$this->import('search tabs search-list search-map search-filter-space');

$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Espaços'), 'url' => $app->createUrl('spaces')],
];
?>

<search page-title="Espaços" entity-type="space" :initial-pseudo-query="{'term:area':[]}">    

    <template #create-button>
        botão criar espaço<!-- <create-space></create-space> -->
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

                    <search-list :pseudo-query="pseudoQuery" type="space">
                        <template #filter>
                            <search-filter-space :pseudo-query="pseudoQuery"></search-filter-space>
                        </template>
                    </search-list>

                </div>
            </tab>
        
            <tab icon="map" label="Mapa" slug="map">
                <div class="search__tabs--map">

                    <search-map type="space" :pseudo-query="pseudoQuery">
                        <template #filter>
                            <search-filter-space :pseudo-query="pseudoQuery" position="map"></search-filter-space>
                        </template>
                    </search-map>

                </div>
            </tab>
        </tabs>
    </template>
</search>