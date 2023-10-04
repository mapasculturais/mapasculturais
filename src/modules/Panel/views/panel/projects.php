<?php

use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card mc-icon create-project');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon project__background"> <mc-icon name="project"></mc-icon> </div>
                <h1 class="title__title"> <?= i::_e('Meus projetos') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar seus projetos') ?>
        </p>
        <div class="panel-page__header-actions">

            <create-project :editable="true" #default="{modal}">
                <button @click="modal.open()" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <span><?= i::__('Criar Projeto') ?></span>
                </button>
            </create-project>
        </div>
    </header>

    <panel--entity-tabs type="project"></panel--entity-tabs>
</div>