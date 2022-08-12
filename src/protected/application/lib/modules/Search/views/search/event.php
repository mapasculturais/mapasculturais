<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb search-header tabs');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Eventos'), 'url' => $app->createUrl('events')],
];
?>

<div class="search">
    <mapas-breadcrumb></mapas-breadcrumb>

    <search-header class="search__header" type="event">
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
                    
                    <tab icon="event" label="Agenda" slug="primary">
                        <h2>Conteúdo principal</h2>
                    </tab>
                    <tab icon="map" label="Mapa" slug="secondary">
                        <h2>Conteúdo secundário</h2>
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