<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    confirm-before-exit
    entity-admins
    entity-cover
    entity-field
    entity-file
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-links
    entity-owner
    entity-profile
    entity-related-agents
    entity-seals
    entity-social-media
    entity-status
    entity-terms
    link-opportunity
    mc-collapsible
    mc-container
');
?>
<div class="opportunity-basic-info edit-1">
    <mc-container>
        <entity-status v-if="!entity.isModel" :entity="entity"></entity-status>
        <main class="edit-1__perfil-main">
            <div class="stack--sm">
                <div class="edit-1__section">
                    <mc-collapsible :open="true">
                        <template #header>
                            <div class="edit-1__section-heading">
                                <h3 class="edit-1__section-title"><?php i::_e('Informações de apresentação') ?></h3>
                                <p class="edit-1__section-subtitle"><?php i::_e('Os dados inseridos abaixo serão exibidos para todos os usuários da plataforma.') ?></p>
                            </div>
                        </template>
                        <template #body>
                            <div class="grid-12 v-bottom">
                                <entity-cover :entity="entity" classes="col-12"></entity-cover>

                                <div class="col-12 grid-12">
                                    <div class="col-3 sm:col-12">
                                        <entity-profile :entity="entity"></entity-profile>
                                    </div>
                                    <div class="col-12 sm:col-12 grid-12 v-bottom">
                                        <entity-field :entity="entity" prop="name" classes="col-12"></entity-field>
                                        <entity-field :entity="entity" label="<?php i::esc_attr_e('Tipo da oportunidade') ?>" prop="type" classes="col-12"></entity-field>
                                    </div>
                                </div>

                                <entity-terms :entity="entity" classes="col-12" taxonomy="area" title="<?php i::_e('Área de interesse') ?>" editable></entity-terms>
                                <entity-terms :entity="entity" classes="col-12" taxonomy="tag" title="<?php i::_e('Tags') ?>" editable></entity-terms>

                                <entity-field :entity="entity" classes="col-12" prop="shortDescription" :max-length="400"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="longDescription"></entity-field>
                            </div>
                        </template>
                    </mc-collapsible>
                </div>

                <div class="edit-1__section">
                    <mc-collapsible :open="true">
                        <template #header>
                            <div class="edit-1__section-heading">
                                <h3 class="edit-1__section-title"><?php i::_e('Administração e relações') ?></h3>
                            </div>
                        </template>
                        <template #body>
                            <div class="grid-12">
                                <link-opportunity :entity="entity" editable class="col-12"></link-opportunity>
                                <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                                <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                                <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>" editable></entity-related-agents>
                                <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                            </div>
                        </template>
                    </mc-collapsible>
                </div>

                <div class="edit-1__section">
                    <mc-collapsible :open="true">
                        <template #header>
                            <div class="edit-1__section-heading">
                                <h3 class="edit-1__section-title"><?php i::_e('Arquivos, links e galerias') ?></h3>
                            </div>
                        </template>
                        <template #body>
                            <div class="grid-12">
                                <entity-file :entity="entity" titleModal="<?php i::_e('Adicionar regulamento') ?>" groupName="rules" classes="col-12" title="<?php i::esc_attr_e('Adicionar regulamento'); ?>" editable></entity-file>
                                <entity-files-list :entity="entity" classes="content-fileList col-12" group="downloads" title="<?php i::esc_attr_e('Adicionar arquivos'); ?>" editable></entity-files-list>
                                <entity-links :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Adicionar links'); ?>" editable></entity-links>
                                <entity-gallery-video :entity="entity" classes="col-12" editable></entity-gallery-video>
                                <entity-gallery :entity="entity" classes="col-12" editable></entity-gallery>
                            </div>
                        </template>
                    </mc-collapsible>
                </div>

                <div class="edit-1__section edit-1__section--social">
                    <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>
                </div>
            </div>
        </main>
    </mc-container>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>
