<?php
use MapasCulturais\i;

$this->import('loading confirm-button');
?>

<div class="entity-actions">
    <?php $this->applyTemplateHook('entity-actions', 'before'); ?>
    <div class="entity-actions__content">
        <?php $this->applyTemplateHook('entity-actions', 'begin'); ?>
        <loading :entity="entity"></loading>
        <template v-if="!entity.__processing">
            <?php $this->applyTemplateHook('entity-actions--leftGroupBtn', 'before'); ?>
            <div class="entity-actions__content--groupBtn rowBtn">
                <?php $this->applyTemplateHook('entity-actions--leftGroupBtn', 'begin'); ?>
                <confirm-button v-if="entity.currentUserPermissions?.archive" @confirm="entity.archive()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--icon button--sm arquivar">
                            <mc-icon name="archive"></mc-icon>
                            <?php i::_e("Arquivar") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Deseja arquivar esse agente?') ?>
                    </template>
                </confirm-button>
                <confirm-button v-if="entity.currentUserPermissions?.remove" @confirm="entity.delete()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--icon button--sm excluir">
                            <mc-icon name="trash"></mc-icon>
                            <?php i::_e("Excluir") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Deseja remover esse agente?') ?>
                    </template>
                </confirm-button>
                <?php $this->applyTemplateHook('entity-actions--leftGroupBtn', 'end'); ?>
            </div>
            <?php $this->applyTemplateHook('entity-actions--leftGroupBtn', 'after'); ?>

            <?php $this->applyTemplateHook('entity-actions--rightGroupBtn', 'before'); ?>
            <div v-if="editable" class="entity-actions__content--groupBtn">
                <?php $this->applyTemplateHook('entity-actions--rightGroupBtn', 'begin'); ?>
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
                <button v-if="entity.currentUserPermissions?.modify" @click="entity.save()" class="button button--md button--secondary">
                    <?php i::_e("Salvar") ?>
                </button>
                <button v-if="entity.status == 0 && entity.currentUserPermissions?.publish" @click="entity.publish()" class="button button--md publish">
                    <?php i::_e("Publicar") ?>
                </button>
                <button v-if="entity.status == 1 && entity.currentUserPermissions?.modify" @click="save()" class="button button--md publish publish-exit">
                    <?php i::_e("Concluir Edição e Sair") ?>
                </button>
                <?php $this->applyTemplateHook('entity-actions--rightGroupBtn', 'end'); ?>
            </div>
            <div v-if="!editable" class="entity-actions__content--groupBtn">
                <?php $this->applyTemplateHook('entity-actions--rightGroupBtn', 'begin'); ?>
                <a :href="entity.editUrl" class="button button button--md publish">
                    <?php i::_e('Editar') ?> {{entityType}}
                </a>
                <?php $this->applyTemplateHook('entity-actions--rightGroupBtn', 'end'); ?>
            </div>
            <?php $this->applyTemplateHook('entity-actions--rightGroupBtn', 'after'); ?>
        </template>
        <?php $this->applyTemplateHook('entity-actions', 'end'); ?>
    </div>
    <?php $this->applyTemplateHook('entity-actions', 'after'); ?>
</div>