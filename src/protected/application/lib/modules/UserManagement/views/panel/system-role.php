<?php
use MapasCulturais\i;
$this->layout = 'panel';

$this->import('
    loading,messages
    tabs,tab
    entities,field
    panel--entity-tabs
    system-roles--list, system-roles--create-modal
');

?>
<div id="main-app" class="panel-list panel-main-content">
    <header class="panel-header clearfix">
        <h2><?php i::_e('Papeis do sistema') ?></h2>
        <messages></messages>
        <div style="float: right;">
            <system-roles--create-modal list="system-role:publish"><?php i::_e("adicionar novo papel") ?></system-roles--create-modal>
        </div>
    </header>
    
    <panel--entity-tabs type="system-role" user="" select="id,status,name,slug,permissions" #={entity}>
        {{entity.id}} - {{entity.slug}}
        <code>{{entity.permissions}}</code>
    </panel--entity-tabs>
</div>