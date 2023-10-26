<?php

use MapasCulturais\i;

$this->import('
    create-agent 
    mc-icon
    panel--entity-actions 
    panel--entity-card 
    panel--entity-tabs 
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon agent__background"> <mc-icon name="agent"></mc-icon> </div>
                <h1 class="title__title"> <?= i::_e('Meus agentes') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você visualiza e gerencia seu perfil de usuário e outros agentes criados') ?>
        </p>
        <div class="panel-page__header-actions">
            <create-agent :editable="true" #default="{modal}">
                <button @click="modal.open()" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <span><?= i::__('Criar agente') ?></span>
                </button>
            </create-agent>
        </div>
    </header>

    <panel--entity-tabs type="agent"></panel--entity-tabs>
</div>