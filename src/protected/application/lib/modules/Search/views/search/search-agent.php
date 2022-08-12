<?php 
use MapasCulturais\i;
 
$this->import('mapas-breadcrumb mc-map create-agent');
$this->breadcramb = [
    ['label'=> i::__('Inicio'), 'url' => $app->createUrl('index')],
    ['label'=> i::__('Agentes'), 'url' => $app->createUrl('agents')],
];
?>


<div class="search-agent">
    <mapas-breadcrumb></mapas-breadcrumb>
    <div class="search-agent__header">
        <create-agent></create-agent>
    </div>
    <div class="search-agent__map">
        <mc-map></mc-map>
    </div>
</div>
