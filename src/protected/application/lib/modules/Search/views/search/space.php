<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb mapas-card mapas-container search-list search-map search-header tabs');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Espaços'), 'url' => $app->createUrl('spaces')],
];
?>

<div class="search">
    <mapas-breadcrumb></mapas-breadcrumb>

    <search-header class="search__header" type="space">
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
                        <mapas-container>
                            <main>
                                <search-list type="space"></search-list>
                            </main>
                            <aside>
                                <mapas-card></mapas-card>
                            </aside>
                        </mapas-container>
                    </tab>

                    <tab icon="map" label="Mapa" slug="map">
                        <search-map type="space"></search-map>
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
