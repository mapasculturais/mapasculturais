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

    <panel--entity-tabs type="event">
        <template #entity-actions-left={entity}>
            <div class="actions-left">
                <panel--entity-actions v-if="entity.status!=-2" :entity="entity" buttons="archive" archive="<?php i::esc_attr_e('Arquivar') ?>"></panel--entity-actions>
                <panel--entity-actions v-if="entity.status!=0 && entity.status!=-2" :entity=" entity" buttons="unpublish" unpublish="<?php i::esc_attr_e('Tornar Rascunho') ?>"></panel--entity-actions>
                <panel--entity-actions :entity="entity" buttons="delete" delete="<?php i::esc_attr_e('Excluir') ?>"></panel--entity-actions>


            </div>
        </template>

        <template #entity-actions-right={entity}>
            <a :href="entity.singleUrl" class="button button--primary-outline button--icon"><?php i::_e('Acessar') ?> <mc-icon name="arrow-right"></mc-icon></a>
            <panel--entity-actions :entity="entity" buttons="publish" publish="<?php i::esc_attr_e('Publicar') ?>"></panel--entity-actions>
        </template>
    </panel--entity-tabs>
</div>