<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb tabs search-header mc-map create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Eventos'), 'url' => $app->createUrl('events')],
];
?>


<div class="events">
    <mapas-breadcrumb></mapas-breadcrumb>
        <search-header type="event">
            <template #create>
                <create-agent></create-agent>
            </template>
            
        </search-header>
</div>
