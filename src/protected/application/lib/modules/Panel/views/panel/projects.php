<?php
use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card');
?>
<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2><?php i::_e('Meus Projetos') ?></h2>
    </header>
    
    <panel--entity-tabs type="project"></panel--entity-tabs>
</div>