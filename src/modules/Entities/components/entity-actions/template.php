<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-confirm-button
    mc-loading
    mc-modal
    mc-popover
    opportunity-create-model
    opportunity-create-based-model
    opportunity-exporter
');
?>
<div v-if="!empty" class="entity-actions">
    <?php $this->applyTemplateHook('entity-actions', 'before') ?>
    <div v-show="entity.__processing" class="entity-actions__content">
        <mc-loading :entity="entity"></mc-loading>
    </div>

    <div v-show="!entity.__processing" class="entity-actions__content">
        <?php $this->applyTemplateHook('entity-actions', 'begin') ?>
        <div class="entity-actions__content--groupBtn" ref="buttons">
            <mc-popover v-if="hasMoreActions" classes="entity-actions__dropdown" title="<?= i::esc_attr__('Mais ações') ?>">
                <template #button="{ toggle }">
                    <button type="button" @click="toggle()" class="button button--icon button--md publish publish-exit entity-actions__more">
                        <mc-icon name="more"></mc-icon>
                        <span><?php i::_e('Mais ações') ?></span>
                        <mc-icon name="down"></mc-icon>
                    </button>
                </template>

                <template #default="{ close }">
                    <div class="entity-actions__menu" @click.capture="onMoreActionsClick($event, close)">
                        <?php $this->applyTemplateHook('entity-actions--primary', 'begin') ?>

                        <mc-confirm-button v-if="entity.currentUserPermissions?.modify && entity.status != -2 && entity.__objectType == 'opportunity' && entity.isModel != 1" @confirm="entity.duplicate()" no="Cancelar" yes="Continuar">
                            <template #button="modal">
                                <button type="button" @click="modal.open()" class="button button--text button--icon button--sm entity-actions__menu-item">
                                    <mc-icon name="copy"></mc-icon>
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

                        <template v-if="entity.currentUserPermissions?.modify && entity.status != -2 && entity.__objectType == 'opportunity' && entity.isModel != 1">
                            <opportunity-create-model :entity="entity"></opportunity-create-model>
                        </template>

                        <template v-if="entity.currentUserPermissions?.modify && entity.status != -2 && entity.__objectType == 'opportunity'">
                            <opportunity-exporter :entity="entity"></opportunity-exporter>
                        </template>

                        <hr v-if="hasExtraActions && hasAdminActions" class="entity-actions__menu-divider">

                        <mc-confirm-button v-if="usePrivate && entity.status == 1 && entity.currentUserPermissions?.makePrivate" @confirm="makePrivate()">
                            <template #button="modal">
                                <button type="button" @click="modal.open()" class="button button--text button--icon button--sm entity-actions__menu-item private">
                                    <mc-icon name="private"></mc-icon>
                                    <?php i::_e("Tornar privado") ?>
                                </button>
                            </template>
                            <template #message="message">
                                <?php i::_e('Você está certo que deseja tornar esta entidade privada?') ?>
                            </template>
                        </mc-confirm-button>

                        <mc-confirm-button v-if="entity.currentUserPermissions?.archive && entity.status != -2" @confirm="entity.archive()">
                            <template #button="modal">
                                <button type="button" @click="modal.open()" class="button button--text button--icon button--sm entity-actions__menu-item arquivar">
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
                                <button type="button" @click="modal.open()" class="button button--text button--icon button--sm entity-actions__menu-item entity-actions__danger excluir">
                                    <mc-icon name="trash"></mc-icon>
                                    <?php i::_e("Excluir") ?>
                                </button>
                            </template>
                            <template #message="message">
                                <?php i::_e('Você está certo que deseja excluir?') ?>
                            </template>
                        </mc-confirm-button>

                        <?php $this->applyTemplateHook('entity-actions--primary', 'end') ?>
                    </div>
                </template>
            </mc-popover>

            <?php $this->applyTemplateHook('entity-actions--leftGroupBtn', 'after'); ?>

            <template v-if="editable">
                <?php $this->applyTemplateHook('entity-actions--secondary', 'begin') ?>
                <mc-confirm-button v-if="entity.status == 0" @confirm="exit()">
                    <template #button="modal">
                        <button type="button" @click="modal.open()" class="button button--icon button--md publish publish-exit">
                            <mc-icon name="logout"></mc-icon>
                            <?php i::_e("Sair") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Deseja sair?') ?>
                    </template>
                </mc-confirm-button>

                <button v-if="entity.status == 1 && entity.currentUserPermissions?.modify" type="button" @click="exit()" class="button button--icon button--md publish publish-exit">
                    <mc-icon name="logout"></mc-icon>
                    <?php i::_e("Sair") ?>
                </button>

                <button v-if="entity.currentUserPermissions?.modify" type="button" @click="save()" class="button button--icon button--md publish publish-exit">
                    <mc-icon name="save"></mc-icon>
                    <?php i::_e("Salvar") ?>
                </button>

                <mc-modal v-if="usePrivate && (entity.status == 0 || entity.status == -2) && entity.currentUserPermissions?.publish" title="<?= i::esc_attr__("Publicar") ?>">
                    <template #default="modal">
                        <p class=""><?= i::__("Você deseja publicar esta entidade de maneira <strong>pública</strong> ou <strong>privada</strong>?") ?></p>
                        <p><?= i::__("Entidades privadas só são visualizadas por administradores ou por agentes relacionados à entidade") ?></p>
                    </template>

                    <template #actions="modal">
                        <button @click="modal.close()" class="button button--text"><?php i::_e("Cancelar") ?></button>
                        <button @click="makePrivate(modal)" class="button button--icon button--sm button--outline button--secondary">
                            <mc-icon name='private'></mc-icon>
                            <?= i::__('Privada') ?>
                        </button>
                        <button @click="publish(modal)" class="button button--primary">
                            <?= i::__('Pública') ?>
                        </button>
                    </template>

                    <template #button="modal">
                        <button type="button" @click="entity.save().then(() => modal.open())" class="button button--icon button--md publish publish-exit">
                            <mc-icon name="upload"></mc-icon>
                            <?= i::__("Salvar e publicar") ?>
                        </button>
                    </template>
                </mc-modal>

                <mc-confirm-button v-if="(!usePrivate || entity.status == -100) && (entity.status == 0 || entity.status == -2 || entity.status == -100) && entity.currentUserPermissions?.publish" @confirm="publish()">
                    <template #button="modal">
                        <button type="button" @click="modal.open()" class="button button--icon button--md publish publish-exit">
                            <mc-icon name="upload"></mc-icon>
                            <?php i::_e("Salvar e publicar") ?>
                        </button>
                    </template>
                    <template #message="message">
                        <?php i::_e('Você está certo que deseja publicar esta entidade?') ?>
                    </template>
                </mc-confirm-button>

                <?php $this->applyTemplateHook('entity-actions--secondary', 'end') ?>
            </template>

            <template v-if="!editable">
                <?php $this->applyTemplateHook('entity-actions--secondary', 'begin') ?>
                <a v-if="entity.currentUserPermissions?.modify && entity.__objectType=='opportunity'" :href="entity.editUrl" class="button button--icon button--md publish publish-exit">
                    <mc-icon name="settings"></mc-icon>
                    <?php i::_e('Gerenciar') ?> {{entityType}}
                </a>
                <a v-if="entity.currentUserPermissions?.modify && entity.__objectType!='opportunity'" :href="entity.editUrl" class="button button--icon button--md publish publish-exit">
                    <mc-icon name="edit"></mc-icon>
                    <?php i::_e('Editar') ?> {{entityType}}
                </a>
                <?php $this->applyTemplateHook('entity-actions--secondary', 'end') ?>
            </template>
        </div>
        <?php $this->applyTemplateHook('entity-actions', 'end') ?>
    </div>
    <?php $this->applyTemplateHook('entity-actions', 'after') ?>
</div>
