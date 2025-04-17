<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-loading
    opportunity-create-model
    opportunity-create-based-model
');
?>
<div v-if="!empty" class="entity-actions">
    <?php $this->applyTemplateHook('entity-actions', 'before') ?>
    <div class="entity-actions__content">
        <?php $this->applyTemplateHook('entity-actions', 'begin'); ?>
        <mc-loading :entity="entity"></mc-loading>
        <template v-if="!entity.__processing">
            <?php $this->applyTemplateHook('entity-actions', 'begin') ?>

            <div class="entity-actions__content--groupBtn rowBtn" ref="buttons1">
                <?php $this->applyTemplateHook('entity-actions--primary', 'begin') ?>

                <mc-confirm-button v-if="entity.currentUserPermissions?.archive && entity.status != -2" @confirm="entity.archive()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--icon button--sm arquivar">
                            <mc-icon name="archive"></mc-icon>
                            <?php i::_e("Arquivar") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você está certo que deseja arquivar?') ?>
                    </template>
                </mc-confirm-button>
                <mc-confirm-button v-if="entity.currentUserPermissions?.remove && canDelete" @confirm="entity.delete()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--icon button--sm excluir">
                            <mc-icon name="trash"></mc-icon>
                            <?php i::_e("Excluir") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você está certo que deseja excluir?') ?>
                    </template>
                </mc-confirm-button>
                <mc-confirm-button v-if="entity.currentUserPermissions?.modify && entity.status != -2 && entity.__objectType == 'opportunity' && entity.isModel != 1" @confirm="entity.duplicate()" no="Cancelar" yes="Continuar">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--icon button--sm">
                            <?php i::_e("Duplicar oportunidade") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <h4><b><?php i::_e('Duplicar modelo'); ?></b></h4>
                        <br>
                        <p><?php i::_e('Todas as configurações atuais da oportunidade, incluindo o vínculo<br> com a entidade associada e os campos de formulário criados, serão<br> duplicadas.') ?></p>
                        <p><?php i::_e('Deseja continuar?') ?></p>
                    </template>
                </mc-confirm-button>
                <div v-if="entity.currentUserPermissions?.modify && entity.status != -2 && entity.__objectType == 'opportunity' && entity.isModel != 1">
                    <opportunity-create-model :entity="entity" classes="col-12"></opportunity-create-model>
                </div> 
                <?php $this->applyTemplateHook('entity-actions--primary', 'end') ?>
            </div>
            <?php $this->applyTemplateHook('entity-actions--leftGroupBtn', 'after'); ?>

            <div v-if="editable" class="entity-actions__content--groupBtn" ref="buttons2">
                <?php $this->applyTemplateHook('entity-actions--secondary', 'begin') ?>
                <mc-confirm-button v-if="entity.status == 0" @confirm="exit()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--md publish publish-exit">
                            <?php i::_e("Sair") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Deseja sair?') ?>
                    </template>
                </mc-confirm-button>
                <button v-if="entity.currentUserPermissions?.modify" @click="save()" class="button button--md publish publish-exit">
                    <?php i::_e("Salvar") ?>
                </button>
                <mc-confirm-button v-if="(entity.status == 0 || entity.status == -2) && entity.currentUserPermissions?.publish" @confirm="entity.publish()">
                    <template #button="modal">
                        <button @click="modal.open()" class="button button--md publish publish-exit">
                            <?php i::_e("Salvar e publicar") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você está certo que deseja publicar esta entidade?') ?>
                    </template>
                </mc-confirm-button>
                <button v-if="entity.status == 1 && entity.currentUserPermissions?.modify" @click="exit()" class="button button--md publish publish-exit">
                    <?php i::_e("Sair") ?>
                </button>

                <?php $this->applyTemplateHook('entity-actions--secondary', 'end') ?>
            </div>

            <div v-if="!editable" class="entity-actions__content--groupBtn" ref="buttons2">
                <?php $this->applyTemplateHook('entity-actions--secondary', 'begin') ?>
                <a v-if="entity.currentUserPermissions?.modify && entity.__objectType=='opportunity'" :href="entity.editUrl" class="button button button--md publish">
                    <?php i::_e('Gerenciar') ?> {{entityType}}
                </a>
                <a v-if="entity.currentUserPermissions?.modify && entity.__objectType!='opportunity'" :href="entity.editUrl" class="button button button--md publish">
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