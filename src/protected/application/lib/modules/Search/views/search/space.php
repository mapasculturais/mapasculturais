<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search-header mc-map create-agent');
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
                    
                    <tab icon="list" label="Lista" slug="primary">
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
