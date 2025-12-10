<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    confirm-before-exit
    country-address-form
    elderly-person
    entity-actions
    entity-admins
    entity-cover
    entity-field
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-owner
    entity-profile
    entity-related-agents
    entity-renew-lock
    entity-social-media
    entity-terms
    entity-status
    mc-breadcrumb
    mc-card
    mc-container
    mc-tabs
    mc-tab
    entity-file
    entity-registration
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <entity-renew-lock :entity="entity"></entity-renew-lock>
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs','begin') ?>
        <mc-tab label="<?= i::_e('Perfil') ?>" slug="info">
            <?php $this->applyTemplateHook('entity-info-validation','begin') ?>
            <mc-container>
                <entity-status :entity="entity"></entity-status>
                <mc-card class="feature">
                    <template #title>
                        <label><?php i::_e("Informações de Apresentação") ?></label>
                        <p><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
                    </template>
                    <template #content>
                        <div class="left">
                            <div class="grid-12 v-bottom">
                                <entity-cover :entity="entity" classes="col-12"></entity-cover>

                                <div class="col-12 grid-12">
                                    <?php $this->applyTemplateHook('entity-info','begin') ?>
                                    <div class="col-3 sm:col-12">
                                        <entity-profile :entity="entity"></entity-profile>
                                    </div>
                                    <div class="col-12 sm:col-12 grid-12 v-bottom">
                                        <entity-field :entity="entity" classes="col-12" prop="name" label="<?php i::_e('Nome de perfil') ?>"></entity-field>
                                    </div>
                                    <?php $this->applyTemplateHook('entity-info','end') ?>
                                </div>
                                
                                <?php $this->applyTemplateHook('edit1-entity-info-taxonomie-area','before') ?>
                                    <entity-terms :entity="entity" taxonomy="area" editable classes="col-12" title="<?php i::_e('Áreas de atuação'); ?>"></entity-terms>
                                    <entity-terms :entity="entity" taxonomy="funcao" editable classes="col-12" title="<?php i::_e('Função(õs) na cultura'); ?>"></entity-terms>
                                    <entity-terms :entity="entity" taxonomy="tag" classes="col-12" title="Tags" editable></entity-terms>
                                <?php $this->applyTemplateHook('edit1-entity-info-taxonomie-area','after') ?>

                                <?php $this->applyTemplateHook('edit1-entity-info-shortDescription','before') ?>
                                <entity-field :entity="entity" classes="col-12" prop="shortDescription" :max-length="400" label="<?php i::_e('Descrição curta') ?>">
                                    <template #info> 
                                        <?php $this->info('cadastro -> cadastrando-usuario -> mini-bio') ?>
                                    </template>
                                </entity-field>
                                <?php $this->applyTemplateHook('edit1-entity-info-shortDescription','after') ?>

                                <?php $this->applyTemplateHook('edit1-entity-info-site','before') ?>
                                    <entity-field :entity="entity" classes="col-12" prop="longDescription" editable></entity-field>
                                    <entity-field :entity="entity" classes="col-6" prop="site" label="<?php i::_e('Link (URL)') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6" prop="descricaosite"></entity-field>
                                    <entity-field :entity="entity" classes="col-6" prop="emailPublico" label="<?= i::__('E-mail público') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6" prop="telefonePublico" label="<?= i::__('Telefone público com DDD') ?>"></entity-field>

                                <?php $this->applyTemplateHook('edit1-entity-info-site','after') ?>
                            </div>
                        </div>
                       
                    </template>
                </mc-card>
                <main>
                    <mc-card>
                        <template #title>
                            <h3 class="bold"><?php i::_e("Dados Pessoais"); ?> <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais') ?></h3>
                            <p><?php i::_e("Não se preocupe, esses dados não serão exibidos publicamente."); ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-12" prop="nomeSocial" label="<?= i::__('Nome Social') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="nomeCompleto" label="<?= i::__('Nome Completo') ?>"></entity-field>
                                <entity-field v-if="global.auth.is('admin')" :entity="entity" prop="type" @change="entity.save(true).then(() => global.reload())" classes="col-12"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="cpf"></entity-field>
                                <entity-field :disabled="!(entity?.cpf?.length == 14)" :entity="entity" classes="col-12" prop="cpfAnexo" title-modal="<?php i::_e('Anexar CPF - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-cpf" :hide-label="true"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="cnpj" label="<?= i::__('MEI (CNPJ do MEI)') ?>"></entity-field>
                                <entity-field :disabled="!(entity?.cnpj?.length == 18)" :entity="entity" classes="col-12" prop="cnpjAnexo" title-modal="<?php i::_e('Anexar CNPJ - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-cnpj" :hide-label="true"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="emailPrivado" label="<?= i::__('E-mail privado') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone1" label="<?= $this->text('edit-1-agent-phone1', i::__('Telefone privado 1 com DDD')) ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="telefone2" label="<?= $this->text('edit-1-agent-phone2', i::__('Telefone privado 2 com DDD')) ?>"></entity-field>
                                <div class="col-12 divider"></div>
                                <country-address-form :entity="entity" class="col-12"></country-address-form>
                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <h3 class="bold"><?php i::_e("Outros documentos"); ?></h3>
                            <p class="data-subtitle"><?php i::_e("Outros documentos"); ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <p class="col-12 data-subtitle bold"><?php i::_e("CNH"); ?></p>
                                <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhNumero" label="<?= i::__('Número de registro') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhCategoria" label="<?= i::__('Categoria') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhValidade" label="<?= i::__('Validade') ?>"></entity-field>
                                <entity-field :disabled="!(entity?.cnhNumero && entity?.cnhCategoria?.length && entity?.cnhValidade)" :entity="entity" classes="col-12" prop="cnhAnexo" title-modal="<?php i::_e('Anexar CNH - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-cnh" :hide-label="true"></entity-field>
                                <div class="col-12 divider"></div>
                                <p class="col-12 data-subtitle bold"><?php i::_e("RG"); ?></p>
                                <entity-field :entity="entity" classes="col-5 sm:col-12" prop="rgNumero" label="<?= i::__('Documento') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-3 sm:col-12" prop="rgOrgaoEmissor" label="<?= i::__('Órgão Emissor') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-4 sm:col-12" prop="rgUF" label="<?= i::__('UF') ?>"></entity-field>                            
                                <entity-field :disabled="!(entity?.rgNumero && entity?.rgOrgaoEmissor && entity?.rgUF)" :entity="entity" classes="col-12" prop="rgAnexo" title-modal="<?php i::_e('Anexar RG - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-rg" :hide-label="true"></entity-field>

                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #title>
                            <h3 class="bold"><?php i::_e("Dados pessoais sensíveis"); ?> <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais-sensiveis') ?></h3>
                            <p class="data-subtitle"><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente"); ?></p>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="dataDeNascimento" label="<?= i::__('Data de Nascimento') ?>"></entity-field>
                                <elderly-person :entity="entity" ></elderly-person>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="genero" label="<?= i::__('Selecione o Gênero') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="orientacaoSexual" label="<?= i::__('Selecione a Orientação Sexual') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="raca" label="<?= i::__('Selecione a Raça/Cor') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="escolaridade" label="<?= i::__('Selecione a sua Escolaridade') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-6 sm:col-12" prop="agenteItinerante" label="<?= i::__('É agente itinerante?') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12 pcd" prop="pessoaDeficiente" label="<?= i::__('Pessoa com Deficiência') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicional" label="<?= i::__('Comunidades tradicionais') ?>"></entity-field>
                                <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicionalOutros" label="<?= i::__('Não encontrou sua comunidade Tradicional') ?>"></entity-field>
                            </div>
                        </template>
                    </mc-card>
                    <mc-card>
                        <template #content>
                            <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>
                        </template>
                    </mc-card>
                </main>
                <aside>
                    <entity-registration :entity="entity"></entity-registration>
                </aside>
            </mc-container>
            <?php $this->applyTemplateHook('entity-info-validation','end') ?>
        </mc-tab>
        
        <mc-tab label="<?= i::esc_attr_e('PortFólio') ?>" slug="port">
            <mc-container>
                <main>
                    <mc-tabs class="tabs" sync-hash>
                        <mc-tab label="<?= i::esc_attr_e('Arquivos') ?>" slug="arquivos">
                            <mc-card>
                                <template #content>
                                    <p><?php i::_e('Insira arquivos de até <strong>' . $app->getMaxUploadSize() . '</strong>. Os arquivos serão exibidos publicamente e poderão ser baixados por qualquer pessoa.') ?></p>
                                    <entity-files-list :entity="entity" classes="col-12" group="downloads" title="<?php i::_e('Arquivos') ?>" editable hide-title></entity-files-list>
                                </template>
                            </mc-card>
                        </mc-tab>
                        <mc-tab label="<?= i::_e('Links') ?>" slug="links">
                            <mc-card>
                                <template #content>
                                    <p><?php i::_e("Os links serão exibidos publicamente e poderão ser acessados por qualquer pessoa."); ?></p>
                                    <entity-links :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Links'); ?>" editable hide-title></entity-links>
                                </template>
                            </mc-card>
                        </mc-tab>
                        <mc-tab label="<?= i::esc_attr_e('Videos') ?>" slug="videos">
                            <mc-card>
                                <template #content>
                                     <p><?php i::_e("Faça upload do seu vídeo em alguma plataforma de hospedagem de vídeos e insira na plataforma Mapas através da URL. Os vídeos serão exibidos publicamente e poderão ser acessados por qualquer pessoa."); ?></p>
                                    <entity-gallery-video :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Vídeos') ?>" editable hide-title></entity-gallery-video>
                                </template>
                            </mc-card>
                        </mc-tab>
                        <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                            <mc-card>
                                <template #content>
                                    <p><?php i::_e('Insira imagens de até <strong> ' . $app->getMaxUploadSize() . '</strong> .As imagens serão exibidas publicamente e poderão ser baixadas por qualquer pessoa.') ?></p>
                                    <entity-gallery :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Fotos') ?>" editable hide-title></entity-gallery>
                                </template>
                            </mc-card>
                        </mc-tab>
                    </mc-tabs>
                </main>
            </mc-container>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs','end') ?>
    </mc-tabs>
    
    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>