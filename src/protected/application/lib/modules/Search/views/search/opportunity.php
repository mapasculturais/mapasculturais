<?php 
use MapasCulturais\i;
 
$this->import('
    search tabs search-list search-map search-filter-opportunity 
    '); /* create-opportunity */
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Oportunidades'), 'url' => $app->createUrl('opportunities')],
];
?>

<search page-title="Oportunidades" entity-type="opportunity" >    

    <template #create-button>
        Botão criar oportunidade<!-- <create-opportunity></create-opportunity> -->
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

                    <search-list :pseudo-query="pseudoQuery" type="opportunity">
                        <template #filter>
                            <search-filter-opportunity :pseudo-query="pseudoQuery"></search-filter-opportunity>
                        </template>
                    </search-list>

                </div>
            </tab>
        
            <tab icon="map" label="Mapa" slug="map">
                <div class="search__tabs--map">

                    <search-map type="opportunity" :pseudo-query="pseudoQuery">
                        <template #filter>
                            <search-filter-opportunity :pseudo-query="pseudoQuery" position="map"></search-filter-opportunity>
                        </template>
                    </search-map>

                </div>
            </tab>
        </tabs>
    </template>
</search>