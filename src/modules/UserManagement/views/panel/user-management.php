<?php
use MapasCulturais\i; 

$this->import('
    mc-entities 
    mc-icon
    mc-link
    panel--card-user 
    panel--entity-tabs
');

$profile = $app->user->profile;
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon default"> <mc-icon name="user-config"></mc-icon> </div>
                <h1 class="title__title"> <?= i::__('Gestão de usuários') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::__('Gestão dos usuários do sistema') ?>
        </p>
    </header>
    
    <panel--entity-tabs type="user" user="" select="id,email,status,currentUserPermissions,profile.{id,name,type},roles.{id,name,subsite.{id,name}}">
        <template #filters-additional="{query, entities}">
            <mc-entities type="system-role" select="name,slug">
                <template #default="roles">
                    <label> <?= i::__("Filtrar por função:") ?>
                        <select 
                            v-model="query['@roles']" 
                            @change="query['@roles'] || delete query['@roles'];"
                            class="entity-tabs__search-select primary__border--solid">
                            <option :value="undefined"><?= i::__('Exibir todas') ?></option>
                            <option value="saasSuperAdmin" ><?= i::__('Super Administrador da Rede') ?></option>
                            <option value="saasAdmin" ><?= i::__('Administrador da Rede') ?></option>
                            <option value="superAdmin" ><?= i::__('Super Administrador') ?></option>
                            <option value="admin" ><?= i::__('Administrador') ?></option>
                            <option v-for="role in roles.entities" v-bind:value="role.slug">{{role.name}}</option>
                        </select>
                    </label>
                </template>
                <template #empty>
                    <label> <?= i::__("Filtrar por função:") ?>
                        <select 
                            v-model="query['@roles']" 
                            @change="query['@roles'] || delete query['@roles'];"
                            class="entity-tabs__search-select primary__border--solid">
                            <option :value="undefined"><?= i::__('Exibir todas') ?></option>
                            <option value="saasSuperAdmin" ><?= i::__('Super Administrador da Rede') ?></option>
                            <option value="saasAdmin" ><?= i::__('Administrador da Rede') ?></option>
                            <option value="superAdmin" ><?= i::__('Super Administrador') ?></option>
                            <option value="admin" ><?= i::__('Administrador') ?></option>
                        </select>
                    </label>
                </template>
            </mc-entities>
        </template>
        <template #default="{entity,moveEntity}">
            <panel--card-user :entity="entity"></panel--card-user>    
        </template>
    </panel--entity-tabs>
</div>