<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    entity-map
    mc-avatar
    mc-collapsible
    mc-entities
    mc-icon
    mc-link
    complaint-suggestion
    entity-actions
    entity-admins
    entity-data
    entity-description-collapse
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-list
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
    mc-share-links
    mc-modal
    mc-tab
    mc-tabs
    mc-title
    opportunity-list
');

$label = $this->isRequestedEntityMine() ? i::__('Meus agentes') : i::__('Agentes');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('search', 'agents')],
    ['label' => $entity->name, 'url' => $app->createUrl('agent', 'single', [$entity->id])],
];
?>

<div class="main-app single-1">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity">
        <template #actions>
            <mc-modal title="<?= i::__('Compartilhar') ?>" classes="entity-header__share-modal">
                <template #default>
                    <mc-share-links classes="col-12" title="" text="<?php i::esc_attr_e('Veja este link:'); ?>"></mc-share-links>
                    <div class="entity-header__share-copy">
                        <p><?php i::_e('Ou copie o link'); ?></p>
                        <button type="button" class="button button--primary-outline button--icon" onclick="navigator.clipboard.writeText(window.location.href)">
                            <mc-icon name="link"></mc-icon>
                            <?php i::_e('Copiar link'); ?>
                        </button>
                    </div>
                </template>
                <template #button="modal">
                    <button type="button" class="button button--primary-outline button--icon" @click="modal.open()">
                        <mc-icon name="share"></mc-icon>
                        <?php i::_e('Compartilhar'); ?>
                    </button>
                </template>
            </mc-modal>
            <complaint-suggestion
                :entity="entity"
                :show-complaint="false"
                contact-button-label="<?php i::esc_attr_e('Enviar mensagem') ?>"
                contact-button-classes="button button--primary button--icon">
            </complaint-suggestion>
        </template>
    </entity-header>
    <div class="single-1__main-tabs">
        <mc-tabs class="tabs" sync-hash>
            <mc-tab icon="exclamation" label="<?= i::_e('Perfil') ?>" slug="info">
                <mc-container>
                    <!-- Agentes coletivos (filhos do perfil individual) — entre abas Perfil e abas internas -->
                    <div v-if="entity.type?.id == 1 && entity.children?.length > 0" class="single-1__collective-agents">
                        <div class="single-1__collective-agents-card">
                            <div class="single-1__collective-agents-header">
                                <h3 class="single-1__collective-agents-title">
                                    <?php i::_e('Agentes coletivos de'); ?> {{ entity.name }}
                                </h3>
                                <a
                                    v-if="entity.children.length > 0"
                                    class="single-1__collective-agents-see-all"
                                    href="<?= $app->createUrl('search', 'agents') ?>">
                                    <?php i::_e('ver todas'); ?>
                                    <mc-icon name="arrow-right"></mc-icon>
                                </a>
                            </div>
                            <mc-entities
                                type="agent"
                                select="id,name,files.avatar,singleUrl"
                                order="name ASC"
                                :ids="entity.children.slice(0, 3).map((item) => item.id ?? item)"
                                #default="{entities}">
                                <ul v-if="entities.length" class="single-1__collective-agents-list">
                                    <li v-for="agent in entities" :key="agent.id" class="single-1__collective-agents-item">
                                        <mc-link :entity="agent" class="single-1__collective-agents-link">
                                            <mc-avatar :entity="agent" size="small"></mc-avatar>
                                            <span class="single-1__collective-agents-name">{{ agent.name }}</span>
                                        </mc-link>
                                    </li>
                                </ul>
                            </mc-entities>
                        </div>
                    </div>

                    <div class="single-1__inner-tabs">
                        <mc-tabs class="tabs" sync-hash>
                            <mc-tab label="<?= i::_e('Público') ?>" slug="publico">
                                <div class="single-1__presentation-card">
                                    <h2 class="single-1__presentation-title"><?php i::_e('Apresentação'); ?></h2>
                                    <div class="single-1__presentation-content">

                                        <div v-if="entity.terms?.area?.length" class="single-1__presentation-item">
                                            <?php $this->applyTemplateHook('single1-entity-info-taxonomie-area', 'before') ?>
                                            <entity-terms
                                                :entity="entity"
                                                hide-required
                                                classes="col-12"
                                                taxonomy="area"
                                                :title="'<?php i::esc_attr_e('Área(s) de atuação'); ?>' + (entity.terms?.area?.length ? ' (' + entity.terms.area.length + ')' : '')">
                                            </entity-terms>
                                            <?php $this->applyTemplateHook('single1-entity-info-taxonomie-area', 'after') ?>
                                        </div>

                                        <div v-if="entity.terms?.funcao?.length" class="single-1__presentation-item">
                                            <?php $this->applyTemplateHook('single1-entity-info-taxonomie-funcao', 'before') ?>
                                            <entity-terms
                                                :entity="entity"
                                                hide-required
                                                taxonomy="funcao"
                                                classes="col-12"
                                                :title="'<?php i::esc_attr_e('Função(ões) na cultura'); ?>' + (entity.terms?.funcao?.length ? ' (' + entity.terms.funcao.length + ')' : '')">
                                            </entity-terms>
                                            <?php $this->applyTemplateHook('single1-entity-info-taxonomie-funcao', 'after') ?>
                                        </div>

                                        <div v-if="entity.terms?.tag?.length" class="single-1__presentation-item">
                                            <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag', 'before') ?>
                                            <entity-terms
                                                :entity="entity"
                                                hide-required
                                                classes="col-12"
                                                taxonomy="tag"
                                                :title="'<?php i::esc_attr_e('Tags'); ?>' + (entity.terms?.tag?.length ? ' (' + entity.terms.tag.length + ')' : '')">
                                            </entity-terms>
                                            <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag', 'after') ?>
                                        </div>

                                        <div v-if="entity.longDescription" class="single-1__presentation-item single-1__description-block">
                                            <entity-description-collapse
                                                :text="entity.longDescription"
                                                label="<?php i::esc_attr_e('Descrição'); ?>">
                                            </entity-description-collapse>
                                        </div>

                                        <div class="grid-12 single-1__presentation-item single-1__presentation-contacts">
                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" prop="site" label="<?php i::_e('Site') ?>"></entity-data>
                                            </div>
                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" prop="telefone1" label="<?php i::_e('Telefone') ?>"></entity-data>
                                            </div>
                                            <div class="col-4 sm:col-12">
                                                <entity-data :entity="entity" prop="emailPublico" label="<?php i::_e('E-mail público') ?>"></entity-data>
                                            </div>
                                        </div>
                                    </div>
                                </div>

                                <div class="col-12 single-1__social-media">
                                    <mc-card>
                                        <template #content>
                                            <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                                        </template>
                                    </mc-card>
                                </div>

                                <div class="col-12 single-1__connections">
                                    <mc-card>
                                        <template #content>
                                            <span>
                                                <h3 class="single-1__description bold"><?php i::_e('Conexões'); ?></h3>
                                            </span>
                                            <opportunity-list></opportunity-list>
                                            <div class="grid-12 col-12">
                                                <div v-if="entity.spaces?.length > 0 || entity.events?.length > 0 || entity.projects?.length > 0" class="col-12">
                                                    <mc-collapsible v-if="entity.spaces?.length>0" open class="col-12 single-1__connection-item">
                                                        <template #header>
                                                            <mc-title tag="h4" size="medium" open class="bold"><?php i::_e('Espaços'); ?></mc-title>
                                                        </template>
                                                        <template #body>
                                                            <entity-list title="" type="space" :ids="entity.spaces"></entity-list>
                                                        </template>
                                                    </mc-collapsible>

                                                    <mc-collapsible v-if="entity.events?.length>0" open class="col-12 single-1__connection-item">
                                                        <template #header>
                                                            <mc-title tag="h4" size="medium" open class="bold"><?php i::_e('Eventos'); ?></mc-title>
                                                        </template>
                                                        <template #body>
                                                            <entity-list title="" type="event" :ids="entity.events"></entity-list>
                                                        </template>
                                                    </mc-collapsible>

                                                    <mc-collapsible v-if="entity.projects?.length>0" open class="col-12 single-1__connection-item">
                                                        <template #header>
                                                            <mc-title tag="h4" size="medium" open class="bold"><?php i::_e('Projetos'); ?></mc-title>
                                                        </template>
                                                        <template #body>
                                                            <entity-list title="" type="project" :ids="entity.projects"></entity-list>
                                                        </template>
                                                    </mc-collapsible>
                                                </div>
                                            </div>
                                        </template>
                                    </mc-card>
                                </div>
                                <div class="col-12">
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-seals', 'before') ?>
                                    <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações'); ?>"></entity-seals>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-seals', 'after') ?>
                                </div>
                                <complaint-suggestion :entity="entity" classes="col-12" :show-contact="false"></complaint-suggestion>

                            </mc-tab>

                            <mc-tab label="<?= i::_e('Dados pessoais') ?>" slug="dados-pessoais">
                                <div class="single-1__personal-card">
                                    <h2 class="single-1__personal-title"><?php i::_e('Dados pessoais'); ?></h2>
                                    <div class="grid-12 single-1__personal-grid">
                                        <div class="col-4 sm:col-12 single-1__personal-col">
                                            <entity-data :entity="entity" prop="dataDeNascimento" label="<?= i::__('Data de nascimento') ?>"></entity-data>
                                            <entity-data :entity="entity" prop="raca" label="<?= i::__('Raça/cor') ?>"></entity-data>
                                            <entity-data :entity="entity" prop="comunidadesTradicional" label="<?= i::__('Comunidade tradicional') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12 single-1__personal-col">
                                            <entity-data :entity="entity" prop="genero" label="<?= i::__('Gênero') ?>"></entity-data>
                                            <entity-data :entity="entity" prop="escolaridade" label="<?= i::__('Escolaridade') ?>"></entity-data>
                                            <entity-data v-if="entity.agenteItinerante" :entity="entity" prop="agenteItinerante" label="<?= i::__('É agente itinerante?') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12 single-1__personal-col">
                                            <entity-data :entity="entity" prop="orientacaoSexual" label="<?= i::__('Orientação sexual') ?>"></entity-data>
                                            <entity-data :entity="entity" classes="pcd" prop="pessoaDeficiente" label="<?= i::__('Deficiência(s)') ?>"></entity-data>
                                            <entity-data v-if="entity.comunidadesTradicionalOutros" :entity="entity" prop="comunidadesTradicionalOutros" label="<?= i::__('Outra comunidade tradicional') ?>"></entity-data>
                                        </div>
                                    </div>
                                </div>

                                <?php $this->applyTemplateHook('single1-agent-documents', 'before') ?>
                                <template v-if="entity.currentUserPermissions.viewPrivateData">
                                    <div v-if="entity.nomeCompleto || entity.nomeSocial || entity.cpf || entity.cnpj || entity.emailPrivado || entity.telefone1 || entity.telefone2" class="single-1__personal-card">
                                        <h2 class="single-1__personal-title"><?php i::_e('Dados de identificação'); ?></h2>
                                        <div class="grid-12 single-1__personal-grid">
                                            <entity-data v-if="entity.nomeCompleto" :entity="entity" classes="col-4 sm:col-12" prop="nomeCompleto" label="<?php i::_e('Nome completo') ?>"></entity-data>
                                            <entity-data v-if="entity.nomeSocial" :entity="entity" classes="col-4 sm:col-12" prop="nomeSocial" label="<?php i::_e('Nome social') ?>"></entity-data>
                                            <entity-data v-if="entity.cpf" :entity="entity" classes="col-4 sm:col-12" prop="cpf" label="<?php i::_e('CPF') ?>"></entity-data>
                                            <entity-data v-if="entity.cnpj" :entity="entity" classes="col-4 sm:col-12" prop="cnpj" label="<?php i::_e('MEI (CNPJ do MEI)') ?>"></entity-data>
                                            <entity-data v-if="entity.emailPrivado" :entity="entity" classes="col-4 sm:col-12" prop="emailPrivado" label="<?php i::_e('E-mail privado') ?>"></entity-data>
                                            <entity-data v-if="entity.telefone1" :entity="entity" classes="col-4 sm:col-12" prop="telefone1" label="<?php i::_e('Telefone privado 1') ?>"></entity-data>
                                            <entity-data v-if="entity.telefone2" :entity="entity" classes="col-4 sm:col-12" prop="telefone2" label="<?php i::_e('Telefone privado 2') ?>"></entity-data>
                                        </div>

                                        <div v-if="entity.rgNumero || entity.cnhNumero || entity.passaporteNumero" class="single-1__personal-docs-meta">
                                            <h3 class="single-1__personal-subtitle"><?php i::_e('Documentos de identificação'); ?></h3>
                                            <div class="grid-12 single-1__personal-grid">
                                                <entity-data v-if="entity.rgNumero" :entity="entity" classes="col-4 sm:col-12" prop="rgNumero" label="<?php i::_e('RG') ?>"></entity-data>
                                                <entity-data v-if="entity.rgOrgaoEmissor" :entity="entity" classes="col-4 sm:col-12" prop="rgOrgaoEmissor" label="<?php i::_e('Órgão emissor') ?>"></entity-data>
                                                <entity-data v-if="entity.rgUF" :entity="entity" classes="col-4 sm:col-12" prop="rgUF" label="<?php i::_e('UF') ?>"></entity-data>
                                                <entity-data v-if="entity.cnhNumero" :entity="entity" classes="col-4 sm:col-12" prop="cnhNumero" label="<?php i::_e('CNH') ?>"></entity-data>
                                                <entity-data v-if="entity.cnhCategoria" :entity="entity" classes="col-4 sm:col-12" prop="cnhCategoria" label="<?php i::_e('Categoria') ?>"></entity-data>
                                                <entity-data v-if="entity.cnhValidade" :entity="entity" classes="col-4 sm:col-12" prop="cnhValidade" label="<?php i::_e('Validade') ?>"></entity-data>
                                                <entity-data v-if="entity.passaporteNumero" :entity="entity" classes="col-4 sm:col-12" prop="passaporteNumero" label="<?php i::_e('Passaporte') ?>"></entity-data>
                                            </div>
                                        </div>
                                    </div>
                                </template>

                                <div
                                    v-if="entity.files?.['docs-cpf'] || entity.files?.['docs-rg'] || entity.files?.['docs-cnh'] || entity.files?.['docs-passaporte'] || entity.files?.['docs-residencia'] || entity.files?.['docs-vinculo-territorial'] || entity.files?.['docs-raca'] || entity.files?.['docs-pcd'] || entity.files?.['docs-comunidades'] || entity.files?.['docs-certidao-fiscal'] || entity.files?.['docs-certidao-trabalhista'] || entity.files?.['docs-certidao-contas'] || entity.files?.downloads"
                                    class="single-1__personal-card single-1__documents-card">
                                    <h2 class="single-1__personal-title"><?php i::_e('Documentos'); ?></h2>
                                    <div class="single-1__documents-list">
                                        <template v-if="entity.currentUserPermissions.viewPrivateData">
                                            <entity-files-list v-if="entity.files?.['docs-cpf']" :entity="entity" classes="docs-anexo-list" group="docs-cpf" seal-prop="cpfAnexo" title="<?php i::_e('Comprovante de CPF'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-rg']" :entity="entity" classes="docs-anexo-list" group="docs-rg" seal-prop="rgAnexo" title="<?php i::_e('Comprovante de RG'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-cnh']" :entity="entity" classes="docs-anexo-list" group="docs-cnh" seal-prop="cnhAnexo" title="<?php i::_e('Comprovante de CNH'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-passaporte']" :entity="entity" classes="docs-anexo-list" group="docs-passaporte" seal-prop="passaporteAnexo" title="<?php i::_e('Comprovante de Passaporte'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-residencia']" :entity="entity" classes="docs-anexo-list" group="docs-residencia" seal-prop="comprovanteResidenciaAnexo" title="<?php i::_e('Comprovante de Residência'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-vinculo-territorial']" :entity="entity" classes="docs-anexo-list" group="docs-vinculo-territorial" seal-prop="comprovanteVinculoTerritorialAnexo" title="<?php i::_e('Comprovante de Vínculo Territorial'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-raca']" :entity="entity" classes="docs-anexo-list" group="docs-raca" seal-prop="racaAnexo" title="<?php i::_e('Comprovação de Raça/Cor'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-pcd']" :entity="entity" classes="docs-anexo-list" group="docs-pcd" seal-prop="pessoaDeficienciaAnexo" title="<?php i::_e('Comprovação de Pessoa com Deficiência'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-comunidades']" :entity="entity" classes="docs-anexo-list" group="docs-comunidades" seal-prop="comunidadesTradicionalAnexo" title="<?php i::_e('Comprovação de Comunidade Tradicional'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-certidao-fiscal']" :entity="entity" classes="docs-anexo-list" group="docs-certidao-fiscal" seal-prop="certidaoFiscalAnexo" title="<?php i::_e('Certidão de Regularidade Fiscal'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-certidao-trabalhista']" :entity="entity" classes="docs-anexo-list" group="docs-certidao-trabalhista" seal-prop="certidaoTrabalhistaAnexo" title="<?php i::_e('Certidão de Regularidade Trabalhista'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-certidao-contas']" :entity="entity" classes="docs-anexo-list" group="docs-certidao-contas" seal-prop="certidaoPrestacaoContasAnexo" title="<?php i::_e('Certidão de Prestação de Contas'); ?>" view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                        </template>
                                        <entity-files-list v-if="entity.files?.downloads" :entity="entity" group="downloads" title="<?php i::_e('Outros documentos'); ?>" hide-title view-action view-action-label="<?php i::esc_attr_e('ver documento'); ?>"></entity-files-list>
                                    </div>
                                </div>
                                <?php $this->applyTemplateHook('single1-agent-documents', 'after') ?>
                            </mc-tab>

                            <mc-tab label="<?= i::_e('Endereço') ?>" slug="endereco">
                                <div class="single-1__address-card">
                                    <div class="single-1__address-header">
                                        <h2 class="single-1__address-title"><?php i::_e('Dados do endereço'); ?></h2>
                                        <a
                                            v-if="(entity.location?.lat ?? entity.location?.latitude) && (entity.location?.lng ?? entity.location?.longitude)"
                                            class="single-1__address-map-link"
                                            href="#localizacao">
                                            <?php i::_e('ver mapa'); ?>
                                        </a>
                                    </div>

                                    <!-- Endereço brasileiro (En_*) -->
                                    <div
                                        v-if="(entity.address_level0 || entity.En_Pais || 'BR') === 'BR'"
                                        class="grid-12 single-1__address-grid">
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="En_CEP" label="<?php i::_e('CEP') ?>"></entity-data>
                                        </div>
                                        <div class="col-8 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Nome_Logradouro" label="<?php i::_e('Logradouro') ?>"></entity-data>
                                        </div>
                                        <div class="col-2 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Num" label="<?php i::_e('Número') ?>"></entity-data>
                                        </div>
                                        <div class="col-2 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Bairro" label="<?php i::_e('Bairro') ?>"></entity-data>
                                        </div>
                                        <div class="col-8 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Complemento" label="<?php i::_e('Complemento (ou ponto de referência)') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Estado" label="<?php i::_e('Estado') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="En_Municipio" label="<?php i::_e('Município') ?>"></entity-data>
                                        </div>
                                    </div>

                                    <!-- Endereço internacional (address_*) -->
                                    <div v-else class="grid-12 single-1__address-grid">
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_postalCode" label="<?php i::_e('Código postal') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level0" label="<?php i::_e('País') ?>"></entity-data>
                                        </div>
                                        <div class="col-8 sm:col-12">
                                            <entity-data :entity="entity" prop="address_line1" label="<?php i::_e('Endereço') ?>"></entity-data>
                                        </div>
                                        <div class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_line2" label="<?php i::_e('Complemento') ?>"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level1" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level1"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level2" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level2"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level3" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level3"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level4" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level4"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level5" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level5"></entity-data>
                                        </div>
                                        <div v-if="entity.address_level6" class="col-4 sm:col-12">
                                            <entity-data :entity="entity" prop="address_level6"></entity-data>
                                        </div>
                                    </div>

                                    <div id="localizacao" class="single-1__address-location">
                                        <h3 class="single-1__address-subtitle"><?php i::_e('Localização'); ?></h3>
                                        <div
                                            v-if="(entity.location?.lat ?? entity.location?.latitude) && (entity.location?.lng ?? entity.location?.longitude)"
                                            class="single-1__address-map">
                                            <entity-map :entity="entity"></entity-map>
                                        </div>
                                        <div v-else class="single-1__address-map-empty">
                                            <p><?php i::_e('Localização não informada'); ?></p>
                                        </div>
                                    </div>
                                </div>
                            </mc-tab>

                            <mc-tab label="<?= i::_e('Administração') ?>" slug="administracao">
                                <p
                                    v-if="!entity.agentRelations?.['group-admin']?.length"
                                    class="single-1__administration-empty">
                                    <?php i::_e('Essa pessoa não possui administradores.'); ?>
                                </p>

                                <div v-else class="single-1__administration-card">
                                    <h2 class="single-1__administration-title"><?php i::_e('Administradores do perfil'); ?></h2>
                                    <p class="single-1__administration-intro"><?php i::_e("Administradores do perfil podem visualizar e editar os dados públicos e pessoais do agente cultural que administram, além de fazer inscrições em seu nome nas oportunidades vinculadas na plataforma e transferir,editar e/ou excluir suas entidades. A administração dos perfis só e possivel mediante a autorização do proprietário do perfil."); ?></p>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'before') ?>
                                    <entity-admins :entity="entity" variant="list" classes="single-1__administration-admins"></entity-admins>
                                    <?php $this->applyTemplateHook('single1-entity-info-entity-admins', 'after') ?>
                                </div>
                            </mc-tab>
                        </mc-tabs>
                    </div>

                    <aside>
                        <div class="grid-12">
                            <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents', 'before') ?>
                            <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados'); ?>"></entity-related-agents>
                            <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents', 'after') ?>
                        </div>
                    </aside>
                </mc-container>
            </mc-tab>

            <mc-tab label="<?= i::esc_attr_e('Portfólio') ?>" slug="port">
                <mc-container>
                    <main>
                        <div class="single-1__portfolio single-1__inner-tabs">
                            <mc-tabs class="tabs" sync-hash>
                                <mc-tab label="<?= i::esc_attr_e('Arquivos') ?>" slug="arquivos">
                                    <div class="single-1__portfolio-card">
                                        <template v-if="entity.currentUserPermissions.viewPrivateData && (entity.files?.['docs-curriculo'] || entity.files?.['docs-portfolio'])">
                                            <entity-files-list v-if="entity.files?.['docs-curriculo']" :entity="entity" classes="portfolio-files-list" group="docs-curriculo" seal-prop="curriculoAnexo" title="<?php i::_e('Currículo'); ?>" view-action></entity-files-list>
                                            <entity-files-list v-if="entity.files?.['docs-portfolio']" :entity="entity" classes="portfolio-files-list" group="docs-portfolio" seal-prop="portfolioAnexo" title="<?php i::_e('Portfólio'); ?>" view-action></entity-files-list>
                                        </template>
                                        <entity-files-list
                                            v-if="entity.files?.downloads"
                                            :entity="entity"
                                            classes="portfolio-files-list"
                                            group="downloads"
                                            title="<?php i::esc_attr_e('Arquivos para download'); ?>"
                                            hide-title
                                            view-action>
                                        </entity-files-list>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::_e('Links') ?>" slug="links">
                                    <div class="single-1__portfolio-card">
                                        <entity-links :entity="entity" title="<?php i::_e('Links'); ?>" hide-title></entity-links>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::esc_attr_e('Vídeos') ?>" slug="videos">
                                    <div class="single-1__portfolio-card">
                                        <entity-gallery-video :entity="entity" hide-title></entity-gallery-video>
                                    </div>
                                </mc-tab>
                                <mc-tab label="<?= i::esc_attr_e('Imagens') ?>" slug="imagens">
                                    <div class="single-1__portfolio-card">
                                        <entity-gallery :entity="entity" hide-title></entity-gallery>
                                    </div>
                                </mc-tab>
                            </mc-tabs>
                        </div>
                    </main>
                </mc-container>
            </mc-tab>
        </mc-tabs>
    </div>
    <entity-actions :entity="entity"></entity-actions>
</div>
