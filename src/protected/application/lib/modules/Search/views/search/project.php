<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search-header mc-map create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Projetos'), 'url' => $app->createUrl('projects')],
];
?>


<div class="project">
    <mapas-breadcrumb></mapas-breadcrumb>
        <search-header type="project">
            <template #create>
                <create-agent></create-agent>
            </template>
            
        </search-header>
</div>
