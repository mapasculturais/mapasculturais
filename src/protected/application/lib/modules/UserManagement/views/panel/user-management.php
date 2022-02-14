<?php
use MapasCulturais\i;

$this->import('
    loading,messages,
    panel--card-user,
    panel--entity-tabs
');

$profile = $app->user->profile;
?>

<div class="panel__row">
    <h1>
        <iconify icon="mdi:account-multiple-outline"></iconify>
        <?= i::__('Gerenciamento de usuários') ?>
    </h1>
    <a class="panel__help-link" href="#"><?=i::__('Ajuda')?></a>
</div>
<div class="panel__row">
    <p><?=i::__('Gerencia os usuários do sistema')?></p>
</div>

<panel--entity-tabs type="user" user="" select="id,email,status,profile.{id,name,type},roles.{id,name,subsite.{id,name}}">
    <template #default="{entity}">
        <panel--card-user :entity="entity"></panel--card-user>
    </template>
</panel--entity-tabs>

