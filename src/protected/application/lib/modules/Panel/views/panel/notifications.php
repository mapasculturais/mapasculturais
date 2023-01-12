<?php
use MapasCulturais\i;

$this->import('notification-list mc-icon mapas-card');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon primary__background"> <mc-icon name="notification"></mc-icon> </div>
                <div class="title__title"> <?= i::__('Minhas notificações') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?=i::__('Ajuda?')?></a>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::__('Nesta seção você visualiza e gerencia suas notificações') ?>
        </p>
    </header>
    <div class="notifications notifications-panel">
        <notification-list></notification-list>
    </div>
</div>
