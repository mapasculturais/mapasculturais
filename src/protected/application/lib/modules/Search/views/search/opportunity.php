<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search-header mc-map create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Oportunidades'), 'url' => $app->createUrl('opportunity')],
];
?>


<div class="opportunity">
    <mapas-breadcrumb></mapas-breadcrumb>
        <search-header type="opportunity">
            <template #create>
                <create-agent></create-agent>
            </template>
            
        </search-header>
</div>
