<?php

use MapasCulturais\i;

$this->layout = 'panel';

$this->import('
    panel--entity-tabs
    messages
');

?>


<div id="main-app" class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2><?php i::_e('Gestão de usuários') ?></h2>
        <messages></messages>
    </header>

    <panel--entity-tabs type="agent" user="" select="id,status,name" #={entities}>

    </panel--entity-tabs>
</div>