<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-entities 
    mc-icon
    mc-link
    panel--card-user 
    panel--entity-tabs
    fake-user-create
');

$profile = $app->user->profile;
?>

<div class="main-app panel__main" style="margin: auto; max-width: 1170px; padding-bottom:2rem;">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon default"> <mc-icon name="arrow-up"></mc-icon> </div>
                <h1 class="title__title"> <?= i::__('Autenticação Fake') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::__('Autenticação para desenvolvimento') ?>
        </p>
        <fake-user-create> </fake-user-create>
    </header>
    <panel--entity-tabs type="user" user="" select="id,email,status,profile.{id,name,type},roles.{id,name,subsite.{id,name}}">
        <template #filters-additional="{query, entities}">
            <mc-entities type="system-role" select="name,slug">
                <template #default="roles">
                    <label> <?= i::__("Filtrar por função:") ?>
                        <select v-model="query['@roles']" @change="query['@roles'] || delete query['@roles'];" class="entity-tabs__search-select primary__border--solid">
                            <option :value="undefined"><?= i::__('Exibir todas') ?></option>
                            <option value="saasSuperAdmin"><?= i::__('Super Administrador da Rede') ?></option>
                            <option value="saasAdmin"><?= i::__('Administrador da Rede') ?></option>
                            <option value="superAdmin"><?= i::__('Super Administrador') ?></option>
                            <option value="admin"><?= i::__('Administrador') ?></option>
                            <option v-for="role in roles.entities" v-bind:value="role.slug">{{role.name}}</option>
                        </select>
                    </label>
                </template>
                <template #empty>
                    <label> <?= i::__("Filtrar por função:") ?>
                        <select v-model="query['@roles']" @change="query['@roles'] || delete query['@roles'];" class="entity-tabs__search-select primary__border--solid">
                            <option :value="undefined"><?= i::__('Exibir todas') ?></option>
                            <option value="saasSuperAdmin"><?= i::__('Super Administrador da Rede') ?></option>
                            <option value="saasAdmin"><?= i::__('Administrador da Rede') ?></option>
                            <option value="superAdmin"><?= i::__('Super Administrador') ?></option>
                            <option value="admin"><?= i::__('Administrador') ?></option>
                        </select>
                    </label>
                </template>
            </mc-entities>
        </template>
        <template #default="{entity,moveEntity}">
            <panel--card-user :entity="entity">
                <template #entity-actions-right>

                    <mc-link route="auth/fakeLogin" :get-params="{fake_authentication_user_id: entity.id}" class="button button--primary button--icon" icon="arrow-right" right-icon>
                        <?= i::__('Fazer login com este usuário') ?>
                    </mc-link>
                </template>
            </panel--card-user>
        </template>
    </panel--entity-tabs>
</div>