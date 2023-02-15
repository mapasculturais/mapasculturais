<?php

use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card mc-icon create-event');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon event__background"> <mc-icon name="event"></mc-icon> </div>
                <div class="title__title"> <?= i::_e('Meus eventos') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar seus eventos culturais') ?>
        </p>
        <div class="panel-page__header-actions">
            <create-event :editable="true" #default="{modal}">
                <button @click="modal.open()" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <span><?= i::__('Criar Evento') ?></span>
                </button>
            </create-event>
        </div>
    </header>

    <panel--entity-tabs type="event"></panel--entity-tabs>
</div>