<?php

use MapasCulturais\i;

$this->import('create-agent panel--entity-tabs panel--entity-actions panel--entity-card mc-icon');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon agent__background"> <mc-icon name="agent"></mc-icon> </div>
                <div class="title__title"> <?= i::_e('Meus agentes') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
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

    <panel--entity-tabs type="agent">

        <template #entity-actions-right={entity}>
            <panel--entity-actions :entity="entity" buttons="publish" publish="<?php i::esc_attr_e('Publicar') ?>"></panel--entity-actions>
            <a :href="entity.singleUrl" class="button button--primary-outline button--icon"><?php i::_e('Acessar') ?> <mc-icon name="arrow-right"></mc-icon></a>
        </template>
    </panel--entity-tabs>
</div>