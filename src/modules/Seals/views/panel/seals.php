<?php
use MapasCulturais\i;

$this->import('
    create-seal 
    panel--entity-tabs 
    panel--entity-card
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon primary__background"> <mc-icon name="seal"></mc-icon></div>
                <h1 class="title__title"> <?= i::_e('Meus selos') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você visualiza e gerencia seu perfil de usuário e outros selos criados') ?>
        </p>
        <div class="panel-page__header-actions">
            <create-seal #default="{modal}"  >
                <button @click="modal.open()" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <span><?=i::__('Criar selo')?></span>
                </button>
            </create-seal>
        </div>
    </header>
    
    <panel--entity-tabs type="seal"></panel--entity-tabs>
</div>