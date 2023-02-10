<?php

use MapasCulturais\i;

$this->import('panel--entity-tabs panel--entity-card mc-icon create-project');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon project__background"> <mc-icon name="project"></mc-icon> </div>
                <div class="title__title"> <?= i::_e('Meus projetos') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
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

    <panel--entity-tabs type="project">
        <template #entity-actions-left={entity}>
            <div class="actions-left">
                <div class="actions-left__before">
                    <panel--entity-actions v-if="entity.status!=-2" :entity="entity" buttons="archive" archive="<?php i::esc_attr_e('Arquivar') ?>"></panel--entity-actions>
                    <panel--entity-actions v-if="entity.status!=0 && entity.status!=-2 && entity.status!=-10" :entity=" entity" buttons="unpublish" unpublish="<?php i::esc_attr_e('Tornar Rascunho') ?>"></panel--entity-actions>
                </div>
                <div class="actions-left__before">

                    <panel--entity-actions v-if="entity.status!=-10" :entity="entity" buttons="delete" delete="<?php i::esc_attr_e('Excluir') ?>"></panel--entity-actions>
                </div>
            </div>
        </template>

        <template #entity-actions-right={entity}>
            <a :href="entity.singleUrl" class="button button--primary-outline button--icon"><?php i::_e('Acessar') ?> <mc-icon name="arrow-right"></mc-icon></a>
            <panel--entity-actions :entity="entity" buttons="publish" publish="<?php i::esc_attr_e('Publicar') ?>"></panel--entity-actions>
        </template>
    </panel--entity-tabs>
</div>