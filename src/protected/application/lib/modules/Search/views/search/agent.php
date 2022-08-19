<?php 
use MapasCulturais\i;
 
$this->import('
    search tabs search-list search-map search-filter-agent create-agent
    ');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Agentes'), 'url' => $app->createUrl('agents')],
];
?>

<search page-title="Agentes" entity-type="agent" >    

    <template #create-button>
        <create-agent></create-agent>
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

                    <search-list :pseudo-query="pseudoQuery" type="agent">
                        <template #filter>
                            <search-filter-agent :pseudo-query="pseudoQuery"></search-filter-agent>
                        </template>
                    </search-list>

                </div>
            </tab>
        
            <tab icon="map" label="Mapa" slug="map">
                <div class="search__tabs--map">

                    <search-map type="agent" :pseudo-query="pseudoQuery">
                        <template #filter>
                            <search-filter-agent :pseudo-query="pseudoQuery" position="map"></search-filter-agent>
                        </template>
                    </search-map>

                </div>
            </tab>
        </tabs>
    </template>
</search>