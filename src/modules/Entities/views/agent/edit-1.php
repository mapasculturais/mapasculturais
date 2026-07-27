<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    confirm-before-exit
    country-address-form
    elderly-person
    entity-actions
    entity-cover
    entity-field
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-profile
    entity-renew-lock
    entity-social-media
    entity-terms
    entity-status
    mc-breadcrumb
    mc-card
    mc-collapsible
    mc-container
    mc-tabs
    mc-tab
    mc-title
    entity-file
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'edit', [$entity->id])],
];
?>

<div class="main-app edit-1">
    <entity-renew-lock :entity="entity"></entity-renew-lock>
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs','begin') ?>
        <mc-tab label="<?= i::_e('Perfil') ?>" slug="info">
            <?php $this->applyTemplateHook('entity-info-validation','begin') ?>
            <mc-container>
                <entity-status :entity="entity"></entity-status>
                <main class="edit-1__perfil-main">
                    <div class="stack--sm">
                    <div class="edit-1__section">
                        <mc-collapsible :open="true">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title"><?php i::_e("Informações de Apresentação") ?></h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Os dados inseridos abaixo serão exibidos para todos os usuários") ?></p>
                                </div>
                            </template>
                            <template #body>
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
                            </template>
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section">
                        <mc-collapsible :open="false">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title">
                                        <?php i::_e("Dados Pessoais"); ?>
                                        <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais') ?>
                                    </h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Não se preocupe, esses dados não serão exibidos publicamente."); ?></p>
                                </div>
                            </template>
                            <template #body>
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
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section">
                        <mc-collapsible :open="false">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title"><?php i::_e("Outros documentos"); ?></h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Outros documentos"); ?></p>
                                </div>
                            </template>
                            <template #body>
                                <div class="grid-12">
                                    <p class="col-12 data-subtitle bold"><?php i::_e("CNH"); ?></p>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhNumero" label="<?= i::__('Número de registro') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhCategoria" label="<?= i::__('Categoria') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="cnhValidade" label="<?= i::__('Validade') ?>"></entity-field>
                                    <entity-field :disabled="!(entity?.cnhNumero && entity?.cnhCategoria?.length && entity?.cnhValidade)" :entity="entity" classes="col-12" prop="cnhAnexo" title-modal="<?php i::_e('Anexar CNH - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-cnh" :hide-label="true"></entity-field>
                                    <div class="col-12 divider"></div>

                                    <p class="col-12 data-subtitle bold"><?php i::_e("RG"); ?></p>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="rgNumero" label="<?= i::__('Número do RG') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="rgOrgaoEmissor" label="<?= i::__('Órgão Emissor') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-4 sm:col-12" prop="rgUF" label="<?= i::__('UF') ?>"></entity-field>
                                    <entity-field :disabled="!(entity?.rgNumero)" :entity="entity" classes="col-12" prop="rgAnexo" title-modal="<?php i::_e('Anexar RG - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-rg" :hide-label="true"></entity-field>
                                    <div class="col-12 divider"></div>

                                    <p class="col-12 data-subtitle bold"><?php i::_e("Passaporte"); ?></p>
                                    <entity-field :entity="entity" classes="col-12" prop="passaporteNumero" label="<?= i::__('Número do passaporte') ?>"></entity-field>
                                    <entity-field :disabled="!(entity?.passaporteNumero)" :entity="entity" classes="col-12" prop="passaporteAnexo" title-modal="<?php i::_e('Anexar Passaporte - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-passaporte" :hide-label="true"></entity-field>
                                    <div class="col-12 divider"></div>
                                </div>
                            </template>
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section">
                        <mc-collapsible :open="false">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title">
                                        <?php i::_e("Dados pessoais sensíveis"); ?>
                                        <?php $this->info('cadastro -> configuracoes-entidades -> dados-pessoais-sensiveis') ?>
                                    </h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Os dados inseridos abaixo serão registrados apenas no sistemas e não serão exibidos publicamente"); ?></p>
                                </div>
                            </template>
                            <template #body>
                                <div class="grid-12">
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="dataDeNascimento" label="<?= i::__('Data de Nascimento') ?>"></entity-field>
                                    <elderly-person :entity="entity" ></elderly-person>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="genero" label="<?= i::__('Selecione o Gênero') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="orientacaoSexual" label="<?= i::__('Selecione a Orientação Sexual') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="raca" label="<?= i::__('Selecione a Raça/Cor') ?>"></entity-field>
                                    <entity-field :disabled="!entity?.raca" :entity="entity" classes="col-12" prop="racaAnexo" title-modal="<?php i::_e('Anexar comprovação de Raça/Cor - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-raca" :hide-label="true"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="escolaridade" label="<?= i::__('Selecione a sua Escolaridade') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-6 sm:col-12" prop="agenteItinerante" label="<?= i::__('É agente itinerante?') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-12 pcd" prop="pessoaDeficiente" label="<?= i::__('Pessoa com Deficiência') ?>"></entity-field>
                                    <entity-field :disabled="!entity?.pessoaDeficiente?.length" :entity="entity" classes="col-12" prop="pessoaDeficienciaAnexo" title-modal="<?php i::_e('Anexar comprovação de Pessoa com Deficiência - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-pcd" :hide-label="true"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicional" label="<?= i::__('Comunidades tradicionais') ?>"></entity-field>
                                    <entity-field :entity="entity" classes="col-12" prop="comunidadesTradicionalOutros" label="<?= i::__('Não encontrou sua comunidade Tradicional') ?>"></entity-field>
                                    <entity-field :disabled="!entity?.comunidadesTradicional" :entity="entity" classes="col-12" prop="comunidadesTradicionalAnexo" title-modal="<?php i::_e('Anexar comprovação de Comunidade Tradicional - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-comunidades" :hide-label="true"></entity-field>
                                    <div class="col-12 divider"></div>
                                </div>
                            </template>
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section">
                        <mc-collapsible :open="false">
                            <template #header>
                                <div class="edit-1__section-heading">
                                    <h3 class="edit-1__section-title"><?php i::_e("Documentos e Certidões"); ?></h3>
                                    <p class="edit-1__section-subtitle"><?php i::_e("Anexos comprobatórios complementares (residência, vínculo, currículo, portfólio e certidões de regularidade)"); ?></p>
                                </div>
                            </template>
                            <template #body>
                                <div class="grid-12">
                                    <entity-field :entity="entity" classes="col-12" prop="comprovanteResidenciaAnexo" label="<?= i::__('Comprovante de Residência') ?>" title-modal="<?php i::_e('Anexar Comprovante de Residência - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-residencia"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <entity-field :entity="entity" classes="col-12" prop="comprovanteVinculoTerritorialAnexo" label="<?= i::__('Comprovante de Vínculo Territorial') ?>" title-modal="<?php i::_e('Anexar Comprovante de Vínculo Territorial - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-vinculo-territorial"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <entity-field :entity="entity" classes="col-12" prop="curriculoAnexo" label="<?= i::__('Currículo') ?>" title-modal="<?php i::_e('Anexar Currículo - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-curriculo"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <entity-files-list :entity="entity" classes="col-12 docs-portfolio-list" group="docs-portfolio" title="<?php i::_e('Portfólio'); ?>" editable></entity-files-list>
                                    <div class="col-12 divider"></div>
                                    <entity-field :entity="entity" classes="col-12" prop="certidaoFiscalAnexo" label="<?= i::__('Certidão de Regularidade Fiscal') ?>" title-modal="<?php i::_e('Anexar Certidão Fiscal - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-certidao-fiscal"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <entity-field :entity="entity" classes="col-12" prop="certidaoTrabalhistaAnexo" label="<?= i::__('Certidão de Regularidade Trabalhista') ?>" title-modal="<?php i::_e('Anexar Certidão Trabalhista - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-certidao-trabalhista"></entity-field>
                                    <div class="col-12 divider"></div>
                                    <entity-field :entity="entity" classes="col-12" prop="certidaoPrestacaoContasAnexo" label="<?= i::__('Certidão de Prestação de Contas') ?>" title-modal="<?php i::_e('Anexar Certidão de Prestação de Contas - Formatos: (png, jpeg, pdf)') ?>" group-name="docs-certidao-contas"></entity-field>
                                </div>
                            </template>
                        </mc-collapsible>
                    </div>

                    <div class="edit-1__section edit-1__section--social">
                        <entity-social-media :entity="entity" editable classes="col-12"></entity-social-media>
                    </div>
                    </div>
                </main>
            </mc-container>
            <?php $this->applyTemplateHook('entity-info-validation','end') ?>
        </mc-tab>

        <mc-tab label="<?= i::esc_attr_e('Portfólio') ?>" slug="port">
            <mc-container>
                <main>
                    <div class="edit-1__portfolio edit-1__inner-tabs">
                        <mc-tabs class="tabs" sync-hash>
                            <mc-tab label="<?= i::esc_attr_e('Arquivos') ?>" slug="arquivos">
                                <div class="edit-1__portfolio-card">
                                    <p class="edit-1__portfolio-helper"><?php i::_e('Insira arquivos de até <strong>' . $app->getMaxUploadSize() . '</strong>. Os arquivos serão exibidos publicamente e poderão ser baixados por qualquer pessoa.') ?></p>
                                    <entity-files-list
                                        :entity="entity"
                                        classes="portfolio-edit-list"
                                        group="downloads"
                                        title="<?php i::_e('Arquivos') ?>"
                                        editable
                                        hide-title
                                        labeled-actions
                                        button-label="<?php i::esc_attr_e('Adicionar arquivo') ?>"
                                        button-primary>
                                    </entity-files-list>
                                </div>
                            </mc-tab>
                            <mc-tab label="<?= i::_e('Links') ?>" slug="links">
                                <div class="edit-1__portfolio-card">
                                    <p class="edit-1__portfolio-helper"><?php i::_e('Os links serão exibidos publicamente e poderão ser acessados por qualquer pessoa.') ?></p>
                                    <entity-links
                                        :entity="entity"
                                        classes="portfolio-edit-list"
                                        title="<?php i::esc_attr_e('Links'); ?>"
                                        editable
                                        hide-title
                                        labeled-actions
                                        button-label="<?php i::esc_attr_e('Adicionar link') ?>"
                                        button-primary>
                                    </entity-links>
                                </div>
                            </mc-tab>
                            <mc-tab label="<?= i::esc_attr_e('Vídeos') ?>" slug="videos">
                                <div class="edit-1__portfolio-card">
                                    <p class="edit-1__portfolio-helper"><?php i::_e('Faça upload do seu vídeo em alguma plataforma de hospedagem de vídeos e insira na plataforma Mapas através da URL. Os vídeos serão exibidos publicamente e poderão ser acessados por qualquer pessoa.') ?></p>
                                    <entity-gallery-video
                                        :entity="entity"
                                        classes="portfolio-edit-videos"
                                        title="<?php i::esc_attr_e('Vídeos') ?>"
                                        editable
                                        hide-title
                                        labeled-actions
                                        button-label="<?php i::esc_attr_e('Adicionar vídeo') ?>"
                                        button-primary>
                                    </entity-gallery-video>
                                </div>
                            </mc-tab>
                            <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                                <div class="edit-1__portfolio-card">
                                    <p class="edit-1__portfolio-helper"><?php i::_e('Insira imagens de até <strong>' . $app->getMaxUploadSize() . '</strong>. As imagens serão exibidas publicamente e poderão ser baixadas por qualquer pessoa.') ?></p>
                                    <entity-gallery
                                        :entity="entity"
                                        classes="portfolio-edit-images"
                                        title="<?php i::esc_attr_e('Fotos') ?>"
                                        editable
                                        hide-title
                                        button-label="<?php i::esc_attr_e('Adicionar imagem') ?>"
                                        button-primary>
                                    </entity-gallery>
                                </div>
                            </mc-tab>
                        </mc-tabs>
                    </div>
                </main>
            </mc-container>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs','end') ?>
    </mc-tabs>

    <entity-actions :entity="entity" editable></entity-actions>
</div>
<confirm-before-exit :entity="entity"></confirm-before-exit>
