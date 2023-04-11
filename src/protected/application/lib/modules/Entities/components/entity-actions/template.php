<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('loading confirm-button');
?>
<div v-if="!empty" class="entity-actions">
    <?php $this->applyTemplateHook('entity-actions', 'before') ?>
    <div class="entity-actions__content">
        <?php $this->applyTemplateHook('entity-actions', 'begin'); ?>
        <loading :entity="entity"></loading>
        <template v-if="!entity.__processing">
            <?php $this->applyTemplateHook('entity-actions', 'begin') ?>

            <div class="entity-actions__content--groupBtn rowBtn" ref="buttons1">
                <?php $this->applyTemplateHook('entity-actions--primary', 'begin') ?>

                <confirm-button v-if="entity.currentUserPermissions?.archive" @confirm="entity.archive()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--icon button--sm arquivar">
                            <mc-icon name="archive"></mc-icon>
                            <?php i::_e("Arquivar") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você está certo qeu deseja arquivar?') ?>
                    </template>
                </confirm-button>
                <confirm-button v-if="entity.currentUserPermissions?.remove && canDelete" @confirm="entity.delete()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--icon button--sm excluir">
                            <mc-icon name="trash"></mc-icon>
                            <?php i::_e("Excluir") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você está certo que deseja excluir?') ?>
                    </template>
                </confirm-button>

                <?php $this->applyTemplateHook('entity-actions--primary', 'end') ?>
            </div>
            <?php $this->applyTemplateHook('entity-actions--leftGroupBtn', 'after'); ?>

            <div v-if="editable" class="entity-actions__content--groupBtn" ref="buttons2">
                <?php $this->applyTemplateHook('entity-actions--secondary', 'begin') ?>
                <confirm-button v-if="entity.status == 0" @confirm="">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--md button--secondary">
                            <?php i::_e("Sair") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Deseja sair?') ?>
                    </template>
                </confirm-button>
                <button v-if="entity.currentUserPermissions?.modify" @click="entity.save()" class="button button--md publish publish-exit">
                    <?php i::_e("Salvar") ?>
                </button>
                <confirm-button v-if="entity.status == 0 && entity.currentUserPermissions?.publish" @confirm="entity.publish()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--md button--secondary">
                            <?php i::_e("Publicar") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você está certo que deseja publicar esta entidade?') ?>
                    </template>
                </confirm-button>
                <button v-if="entity.status == 1 && entity.currentUserPermissions?.modify" @click="save()" class="button button--md publish publish-exit">
                    <?php i::_e("Concluir Edição e Sair") ?>
                </button>

                <?php $this->applyTemplateHook('entity-actions--secondary', 'end') ?>
            </div>

            <div v-if="!editable" class="entity-actions__content--groupBtn" ref="buttons2">
                <?php $this->applyTemplateHook('entity-actions--secondary', 'begin') ?>
                <a v-if="entity.currentUserPermissions?.modify" :href="entity.editUrl" class="button button button--md publish">
                    <?php i::_e('Editar') ?> {{entityType}}
                </a>
                <?php $this->applyTemplateHook('entity-actions--secondary', 'end') ?>
            </div>
            <?php $this->applyTemplateHook('entity-actions', 'end') ?>
        </template>
        <?php $this->applyTemplateHook('entity-actions', 'end'); ?>
    </div>
    <?php $this->applyTemplateHook('entity-actions', 'after') ?>
</div>