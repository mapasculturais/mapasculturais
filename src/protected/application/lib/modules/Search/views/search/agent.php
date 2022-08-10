<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search-header mc-map create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Agentes'), 'url' => $app->createUrl('agents')],
];
?>


<div class="agent">
    <mapas-breadcrumb></mapas-breadcrumb>
        <search-header type="agent">
            <template #create>
                <create-agent></create-agent>
            </template>
            <template #tabs>
                <label class="search-header__actions--label">Visualizar como</label>

                
            </template>
        </search-header>
</div>
