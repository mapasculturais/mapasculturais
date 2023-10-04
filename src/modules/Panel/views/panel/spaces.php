<?php

use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card mc-icon create-space');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon space__background"> <mc-icon name="space"></mc-icon> </div>
                <h1 class="title__title"> <?= i::_e('Meus espaços') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar seus espaços culturais') ?>
        </p>
        <div class="panel-page__header-actions">
            <create-space :editable="true" #default="{modal}">
                <button @click="modal.open()" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <span><?= i::__('Criar Espaço') ?></span>
                </button>
            </create-space>
        </div>
    </header>

    <panel--entity-tabs type="space"></panel--entity-tabs>
</div>