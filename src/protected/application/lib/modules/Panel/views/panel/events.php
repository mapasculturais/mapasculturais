<?php
use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card');
?>
<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2><?php i::_e('Meus Eventos') ?></h2>
    </header>
    
    <panel--entity-tabs type="event"></panel--entity-tabs>
</div>