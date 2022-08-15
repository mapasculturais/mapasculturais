<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb mapas-card mapas-container search-map search-header search-list tabs create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Agentes'), 'url' => $app->createUrl('agents')],
];
?>


<div class="search">
    <mapas-breadcrumb></mapas-breadcrumb>
        <search-header class="search__header" type="agent">
            <template #create>
                <create-agent></create-agent>
            </template>
            <template #actions>
                <tabs class="search__header--tabs">
                    <template  #before-tablist>
                        <label class="search__header--tabs-label">
                        Visualizar como:
                        </label> 
                    </template>
                    
                    <tab icon="list" label="Lista" slug="list">
                        <mapas-container class="search-list">
                            <search-list type="agent"></search-list>
                        </mapas-container>
                    </tab>

                    <tab icon="map" label="Mapa" slug="map">
                        <search-map type="agent"></search-map>
                    </tab>
                    
                    <template #after-tablist>
                       <div class="search__header--tabs-filter">
                           <input type="text"/>
                       </div>
                    </template>
                </tabs>
            </template>
        </search-header>
</div>


