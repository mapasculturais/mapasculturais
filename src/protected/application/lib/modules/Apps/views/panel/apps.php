<?php

use MapasCulturais\i;

$this->import('
    app-card-content
    mc-icon 
    panel--entity-card
    panel--entity-tabs 
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon app__background">
                    <mc-icon name="app"></mc-icon>
                </div>
                <div class="title__title"> <?= i::_e('Meus aplicativos') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar seus aplicativos') ?>
        </p>
        <div class="panel-page__header-actions">
            <create-apps #default>
                <button @click="modal.open()" class="button button--primary button--icon"><mc-icon name="add"></mc-icon> <?= i::_e('Criar Aplicativo') ?></button>
            </create-apps>
        </div>
    </header>

    <panel--entity-tabs tabs="publish,trash" select="name,privateKey,publicKey,status" type="app">
        <template #card-title="{entity}">
            <label>{{entity.name}}</label>
            <mc-icon class="icon-app" name="edit"></mc-icon>
        </template>
        <template #card-content="{entity}">
            <app-card-content :entity="entity"></app-card-content>
        </template>
        <template #entity-actions-right>
            &nbsp;
        </template>
    </panel--entity-tabs>
</div>