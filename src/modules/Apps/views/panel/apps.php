<?php

use MapasCulturais\i;

$this->import('
    app-card-content
    mc-icon 
    panel--entity-card
    panel--entity-tabs 
    create-app
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon app__background">
                    <mc-icon name="app"></mc-icon>
                </div>
                <h1 class="title__title"> <?= i::_e('Meus aplicativos') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar seus aplicativos') ?>
        </p>
        <div class="panel-page__header-actions">
            <create-app></create-app>
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