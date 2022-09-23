<?php
use MapasCulturais\i;
$this->layout = 'panel';

$this->import('
    confirm-button
    mc-icon
    system-roles--card
    panel--entity-tabs
    system-roles--modal
');

?>
<div class="panel__row">
    <h1>
        <mc-icon name="role"></mc-icon>
        <?= i::__('Funções de usuários') ?>
    </h1>
    <a class="panel__help-link" href="#"><?=i::__('Ajuda')?></a>
</div>
<div class="panel__row">
    <p><?=i::__('Nesta seção você visualiza e gerencia as funções de usuário customizadas.')?></p>
    <system-roles--modal list="system-role:publish"></system-roles--modal>
</div>

<div class="panel-list panel-main-content">
    <panel--entity-tabs type="system-role" user="" select="id,status,name,slug,permissions" #default={entity}>
        <system-roles--card :entity="entity"></system-roles--card>
    </panel--entity-tabs>
</div>