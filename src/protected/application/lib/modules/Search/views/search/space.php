<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search-header mc-map create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Espaços'), 'url' => $app->createUrl('spaces')],
];
?>


<div class="space">
    <mapas-breadcrumb></mapas-breadcrumb>
        <search-header type="space">
            <template #create>
                <create-agent></create-agent>
            </template>
            
        </search-header>
</div>
