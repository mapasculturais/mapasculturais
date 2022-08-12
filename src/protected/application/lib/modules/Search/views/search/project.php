<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search-header mc-map create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Projetos'), 'url' => $app->createUrl('projects')],
];
?>


<div class="search">
    <mapas-breadcrumb></mapas-breadcrumb>
    
    <search-header class="search__header" type="project">
        <template #create>
            <create-agent></create-agent>
        </template>
        <template #actions>
        </template>
    </search-header>
    <div class="search__content">
    <!-- verificar com ux se será adicionado o campo de pesquisa -->
    </div>
</div>
