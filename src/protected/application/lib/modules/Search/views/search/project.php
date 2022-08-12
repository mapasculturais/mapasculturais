<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb mapas-card mapas-container search-list search-map search-header');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Projetos'), 'url' => $app->createUrl('projects')],
];
?>


<div class="search">
    <mapas-breadcrumb></mapas-breadcrumb>
    
    <search-header class="search__header" type="project">
        <template #create>
        </template>
        <template #actions>
            <div class="search__header--filter">
                <input type="text"/>
            </div>
        </template>
    </search-header>

    <div class="search__content">
        <mapas-container>
            <main>
                <search-list type="project"></search-list>
            </main>
            <aside>
                <mapas-card></mapas-card>
            </aside>
        </mapas-container>
    </div>
</div>
