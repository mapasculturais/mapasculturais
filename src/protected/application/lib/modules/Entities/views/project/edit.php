<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    create-project
    entity-actions
    entity-activity-card
    entity-admins
    entity-cover
    entity-field
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-owner
    entity-parent-edit
    entity-profile
    entity-related-agents
    entity-social-media
    entity-terms
    link-project
    mapas-breadcrumb
    mapas-card
    mapas-container
');

$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Meus Projetos'), 'url' => $app->createUrl('panel', 'projects')],
    ['label' => $entity->name, 'url' => $app->createUrl('project', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mapas-container>
        <mapas-card class="feature">
            <template #title>
                <label><?php i::_e("Informações de Apresentação") ?></label>
                <p><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
            </template>
            <template #content>
                <div class="left">
                    <div class="grid-12 v-center">
                        <entity-cover :entity="entity" classes="col-12"></entity-cover>
                        <div class="col-3 sm:col-12">
                            <entity-profile :entity="entity"></entity-profile>
                        </div>
                        <div class="col-9 sm:col-12">
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" label="<?php i::_e("Nome do projeto") ?>" prop="name"></entity-field>
                                <entity-field :entity="entity" classes="col-12" label="<?php i::_e("Tipo do projeto") ?>" prop="type"></entity-field>
                            </div>
                        </div>
                        <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
                        <entity-field :entity="entity" classes="col-12" label="<?php i::_e("Link para página ou site do projeto") ?>" prop="site"></entity-field>
                    </div>
                </div>
                <div class="divider"></div>
                <div class="right">
                    <entity-social-media :entity="entity" classes="col-12" editable></entity-social-media>
                </div>
            </template>
        </mapas-card>
        <main>
            <mapas-card>
                <template #title>
                    <label><?php i::_e("Período de execução do projeto"); ?></label>
                </template>
                <template #content>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" label="<?php i::_e('Data inicial') ?>" prop="startsOn"></entity-field>
                        <entity-field :entity="entity" classes="col-12" label="<?php i::_e('Data Final') ?>" prop="endsOn"></entity-field>
                    </div>
                </template>
            </mapas-card>
            <!-- <mapas-card>
                 <template #title>
                    <label><?php i::_e("Adicione atividades para o seu projeto"); ?></label>
                    <p><?php i::_e("Crie um projeto com informações básicas e de forma rápida."); ?></p>
                </template>
                <template #content>
                    <div class="grid-12">
                        <div class="col-12">
                            <create-project editable></create-project>
                        </div>
                        <div class="col-12">
                            <entity-activity-card :entity="entity" editable></entity-activity-card>
                        </div>
                    </div>
                </template> 
            </mapas-card> -->
            <!-- <mapas-card>
                <template #title>
                    <label><?php i::_e("Adicione um projeto que integrará este**"); ?></label>
                    <p><?php i::_e("Selecione um projeto para que o seu projeto atual seja vinculado como integrante"); ?></p>
                </template>
                <template #content>

                </template>
            </mapas-card> -->
            <mapas-card>
                <template #title>
                    <label><?php i::_e("Adicione um subprojeto"); ?></label>
                    <p><?php i::_e("Adicione um projeto que será vinculado a este"); ?></p>
                </template>
                <template #content>

                </template>
            </mapas-card>
            <!-- <mapas-card>
                <template #title>
                    <label><?php i::_e("Vincule um evento ao seu projeto"); ?></label>
                </template>
                <template #content>
                    <link-project :entity="entity"></link-project>
                </template>
            </mapas-card> -->
            <!-- <mapas-card>
                <template  #title>
                    <label><?php i::_e("Contatos do projeto"); ?></label>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" prop="emailPrivado"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="telefonePublico"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="emailPublico"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="telefone1"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="telefone2"></entity-field>
                        <div class="col-12 divider"></div>
                    </div>
                   

                </template>
                <template #content>

                </template>
            </mapas-card> -->
            <mapas-card>
                <template #title>
                    <label><?php i::_e("Contatos do projeto"); ?></label>
                </template>
                <template #content>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" prop="emailPublico"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="emailPrivado"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="telefonePublico"></entity-field>
                        <entity-field :entity="entity" classes="col-12" label="Telefone privado 1" prop="telefone1"></entity-field>
                        <entity-field :entity="entity" classes="col-12" label="Telefone privado 2" prop="telefone2"></entity-field>
                    </div>
                </template>
            </mapas-card>
            <mapas-card>
                <template #title>
                    <label><?php i::_e("Mais informações públicas"); ?></label>
                    <p><?php i::_e("Os dados inseridos abaixo assim como as informações de apresentação também são exibidos publicamente"); ?></p>
                </template>
                <template #content>
                    <div class="grid-12">
                        <entity-field :entity="entity" classes="col-12" label="<?php i::_e('Descrição') ?>" prop="longDescription"></entity-field>
                        <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?= i::_e('Adicionar arquivos para download') ?>" editable></entity-files-list>
                        <div class="col-12">
                            <entity-links :entity="entity" title="<?php i::_e('Adicionar links'); ?>" editable></entity-links>
                        </div>
                        <entity-gallery-video :entity="entity" classes="col-12" title="<?php i::_e('Adicionar vídeos') ?>" editable></entity-gallery-video>
                        <entity-gallery :entity="entity" classes="col-12" title="<?php i::_e('Adicionar fotos na galeria') ?>" editable></entity-gallery>
                    </div>
                </template>
            </mapas-card>
        </main>
        <aside>
            <mapas-card>
                <template #content>
                    <div class="grid-12">
                        <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                        <entity-terms :entity="entity" editable classes="col-12" taxonomy="tag" title="<?php i::_e('Tags') ?>" ></entity-terms>
                        <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                        <entity-owner :entity="entity" classes="col-12" title="<?php i::_e('Publicado por') ?>" editable></entity-owner>
                        <entity-parent-edit :entity="entity" classes="col-12" type="project" label="<?php i::esc_attr_e('Adicionar Supra Projeto')?>"></entity-parent-edit') ?>">
                    </div>
                </template>
            </mapas-card>
        </aside>
    </mapas-container>
    <entity-actions :entity="entity" editable></entity-actions>
</div>