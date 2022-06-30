<?php
use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card');
?>
<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2><?php i::_e('Meus Eventos') ?></h2>
    </header>
    
    <panel--entity-tabs type="event" select="id,name,terms,files.avatar" #default={entity}>
        <panel--entity-card :entity="entity"></panel--entity-card>
    </panel--entity-tabs>
</div>