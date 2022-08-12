<?php 
use MapasCulturais\i;
 
$this->import('entities entity-card mapas-breadcrumb mapas-card mapas-container search-map search-header tabs mc-map create-agent');
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
                        <mapas-container>
                            <main>
                                <entities type="agent" :query="{'@order' : 'registrationFrom ASC', '@limit' : 20, '@select' : 'id,name,shortDescription,terms,seals,singleUrl'}"> 
                                    <template #default="{entities}">
                                        <div class="grid-12">
                                            <div class="col-12" v-for="entity in entities" :key="entity.id">
                                                <entity-card :entity="entity"></entity-card> 
                                            </div>
                                        </div>
                                    </template>
                                </entities>
                            </main>
                            <aside>
                                <mapas-card></mapas-card>
                            </aside>
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


