<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    agent-data-1
    country-address-view
    complaint-suggestion
    entity-actions
    entity-admins
    entity-data
    entity-files-list
    entity-gallery
    entity-gallery-video
    entity-header
    entity-links
    entity-list
    entity-owner
    entity-related-agents
    entity-seals
    entity-social-media
    entity-terms
    mc-breadcrumb
    mc-card
    mc-container
    mc-share-links
    mc-tab
    mc-tabs
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
    <entity-header :entity="entity"></entity-header>
    <mc-tabs class="tabs" sync-hash>
        <mc-tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
            <mc-container>
                <main>
                    <opportunity-list></opportunity-list>
                    <div class="grid-12 col-12">
                        <agent-data-1 :entity="entity"></agent-data-1>
                        <?php $this->applyTemplateHook('single1-agent-documents','before') ?>
                        <template v-if="entity.currentUserPermissions.viewPrivateData">
                            <div v-if="entity.rgNumero || entity.cnhNumero || entity.passaporteNumero" class="col-12 agent-data">
                                <div class="agent-data__secondTitle">
                                    <h4 class="title bold"><?php i::_e("Documentos") ?></h4>
                                </div>
                                <div class="grid-12">
                                    <div v-if="entity.rgNumero" class="col-4 sm:col-12"><entity-data class="agent-data__fields--field" :entity="entity" prop="rgNumero" label="<?php i::_e('RG') ?>"></entity-data></div>
                                    <div v-if="entity.rgOrgaoEmissor" class="col-4 sm:col-12"><entity-data class="agent-data__fields--field" :entity="entity" prop="rgOrgaoEmissor" label="<?php i::_e('Órgão Emissor') ?>"></entity-data></div>
                                    <div v-if="entity.rgUF" class="col-4 sm:col-12"><entity-data class="agent-data__fields--field" :entity="entity" prop="rgUF" label="<?php i::_e('UF') ?>"></entity-data></div>
                                    <div v-if="entity.rgNumero || entity.rgOrgaoEmissor || entity.rgUF" class="col-12 divider"></div>

                                    <div v-if="entity.cnhNumero" class="col-4 sm:col-12"><entity-data class="agent-data__fields--field" :entity="entity" prop="cnhNumero" label="<?php i::_e('CNH') ?>"></entity-data></div>
                                    <div v-if="entity.cnhCategoria" class="col-4 sm:col-12"><entity-data class="agent-data__fields--field" :entity="entity" prop="cnhCategoria" label="<?php i::_e('Categoria') ?>"></entity-data></div>
                                    <div v-if="entity.cnhValidade" class="col-4 sm:col-12"><entity-data class="agent-data__fields--field" :entity="entity" prop="cnhValidade" label="<?php i::_e('Validade') ?>"></entity-data></div>
                                    <div v-if="entity.cnhNumero || entity.cnhCategoria || entity.cnhValidade" class="col-12 divider"></div>

                                    <div v-if="entity.passaporteNumero" class="col-12"><entity-data class="agent-data__fields--field" :entity="entity" prop="passaporteNumero" label="<?php i::_e('Passaporte') ?>"></entity-data></div>
                                </div>
                            </div>
                            <div v-if="entity.files?.['docs-cpf'] || entity.files?.['docs-rg'] || entity.files?.['docs-cnh'] || entity.files?.['docs-passaporte'] || entity.files?.['docs-residencia'] || entity.files?.['docs-vinculo-territorial'] || entity.files?.['docs-raca'] || entity.files?.['docs-pcd'] || entity.files?.['docs-comunidades']" class="col-12 agent-data">
                                <div class="agent-data__secondTitle">
                                    <h4 class="title bold"><?php i::_e("Comprovantes") ?></h4>
                                </div>
                                <entity-files-list v-if="entity.files?.['docs-cpf']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-cpf" seal-prop="cpfAnexo" title="<?php i::_e('Comprovante de CPF'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-rg']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-rg" seal-prop="rgAnexo" title="<?php i::_e('Comprovante de RG'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-cnh']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-cnh" seal-prop="cnhAnexo" title="<?php i::_e('Comprovante de CNH'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-passaporte']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-passaporte" seal-prop="passaporteAnexo" title="<?php i::_e('Comprovante de Passaporte'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-residencia']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-residencia" seal-prop="comprovanteResidenciaAnexo" title="<?php i::_e('Comprovante de Residência'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-vinculo-territorial']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-vinculo-territorial" seal-prop="comprovanteVinculoTerritorialAnexo" title="<?php i::_e('Comprovante de Vínculo Territorial'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-raca']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-raca" seal-prop="racaAnexo" title="<?php i::_e('Comprovação de Raça/Cor'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-pcd']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-pcd" seal-prop="pessoaDeficienciaAnexo" title="<?php i::_e('Comprovação de Pessoa com Deficiência'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-comunidades']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-comunidades" seal-prop="comunidadesTradicionalAnexo" title="<?php i::_e('Comprovação de Comunidade Tradicional'); ?>"></entity-files-list>
                            </div>
                            <div v-if="entity.files?.['docs-certidao-fiscal'] || entity.files?.['docs-certidao-trabalhista'] || entity.files?.['docs-certidao-contas']" class="col-12 agent-data">
                                <div class="agent-data__secondTitle">
                                    <h4 class="title bold"><?php i::_e("Documentos e Certidões") ?></h4>
                                </div>
                                <entity-files-list v-if="entity.files?.['docs-certidao-fiscal']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-certidao-fiscal" seal-prop="certidaoFiscalAnexo" title="<?php i::_e('Certidão de Regularidade Fiscal'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-certidao-trabalhista']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-certidao-trabalhista" seal-prop="certidaoTrabalhistaAnexo" title="<?php i::_e('Certidão de Regularidade Trabalhista'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-certidao-contas']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-certidao-contas" seal-prop="certidaoPrestacaoContasAnexo" title="<?php i::_e('Certidão de Prestação de Contas'); ?>"></entity-files-list>
                            </div>
                            <div v-if="entity.files?.['docs-curriculo'] || entity.files?.['docs-portfolio']" class="col-12 agent-data">
                                <div class="agent-data__secondTitle">
                                    <h4 class="title bold"><?php i::_e("Portfólio e Currículo") ?></h4>
                                </div>
                                <entity-files-list v-if="entity.files?.['docs-curriculo']" :entity="entity" classes="col-12 docs-anexo-list" group="docs-curriculo" seal-prop="curriculoAnexo" title="<?php i::_e('Currículo'); ?>"></entity-files-list>
                                <entity-files-list v-if="entity.files?.['docs-portfolio']" :entity="entity" classes="col-12 docs-portfolio-list" group="docs-portfolio" seal-prop="portfolioAnexo" title="<?php i::_e('Portfólio'); ?>"></entity-files-list>
                            </div>
                        </template>
                        <?php $this->applyTemplateHook('single1-agent-documents','after') ?>
                        <country-address-view v-if="entity.publicLocation" :entity="entity" class="col-12"></country-address-view>
                        <div v-if="entity.longDescription" class="col-12">
                            <span>   
                                <h3 class="single-1__description bold"><?php i::_e('Descrição Detalhada');?></h3>
                            </span>
                            <p class="description" v-html="entity.longDescription"></p>
                        </div>
                        <entity-files-list v-if="entity.files.downloads!= null" :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
                        <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                        <entity-gallery-video :entity="entity" classes="col-12"></entity-gallery-video>
                        <entity-gallery :entity="entity" classes="col-12"></entity-gallery>
                        <div v-if="entity.spaces?.length > 0 || entity.children?.length > 0 || entity.events?.length > 0 || entity.projects?.length > 0" class="col-12">
                            <h4 class="property-list"> <?php i::_e('Propriedades do Agente:');?> </h4>
                            <entity-list v-if="entity.spaces?.length>0" title="<?php i::esc_attr_e('Espaços');?>" type="space" :ids="entity.spaces"></entity-list>
                            <entity-list v-if="entity.events?.length>0" title="<?php i::esc_attr_e('Eventos');?>" type="event" :ids="entity.events"></entity-list>
                            <entity-list v-if="entity.children?.length>0" title="<?php i::esc_attr_e('Agentes');?>" type="agent" :ids="entity.children"></entity-list>
                            <entity-list v-if="entity.projects?.length>0" title="<?php i::esc_attr_e('Projetos');?>" type="project" :ids="entity.projects"></entity-list>                                
                        </div>
                        <complaint-suggestion :entity="entity" classes="col-12"></complaint-suggestion>
                    </div>
                </main>
                <aside>
                    <div class="grid-12">
                        <?php $this->applyTemplateHook('single1-entity-info-taxonomie-area','before') ?>
                        <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="area" title="<?php i::esc_attr_e('Áreas de atuação');?>"></entity-terms>
                        <?php $this->applyTemplateHook('single1-entity-info-taxonomie-area','after') ?>
                        
                        <?php $this->applyTemplateHook('single1-entity-info-taxonomie-funcao','before') ?>
                        <entity-terms :entity="entity" hide-required taxonomy="funcao" classes="col-12" title="<?php i::_e('Funções'); ?>"></entity-terms>
                        <?php $this->applyTemplateHook('single1-entity-info-taxonomie-funcao','after') ?>

                        <?php $this->applyTemplateHook('single1-entity-info-social-media','before') ?>
                        <entity-social-media :entity="entity" classes="col-12"></entity-social-media>
                        <?php $this->applyTemplateHook('single1-entity-info-social-media','after') ?>
                        
                        <?php $this->applyTemplateHook('single1-entity-info-entity-seals','before') ?>
                        <entity-seals :entity="entity" :editable="entity.currentUserPermissions?.createSealRelation" classes="col-12" title="<?php i::esc_attr_e('Verificações');?>"></entity-seals>
                        <?php $this->applyTemplateHook('single1-entity-info-entity-seals','after') ?>

                        <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents','before') ?>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents> 
                        <?php $this->applyTemplateHook('single1-entity-info-entity-related-agents','after') ?>
                        
                        <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag','before') ?>
                        <entity-terms :entity="entity" hide-required classes="col-12" taxonomy="tag" title="<?php i::esc_attr_e('Tags') ?>"></entity-terms>
                        <?php $this->applyTemplateHook('single1-entity-info-entity-terms-tag','after') ?>
                        
                        <?php $this->applyTemplateHook('single1-entity-info-mc-share-links','before') ?>
                        <mc-share-links  classes="col-12" title="<?php i::esc_attr_e('Compartilhar');?>" text="<?php i::esc_attr_e('Veja este link:');?>"></mc-share-links>
                        <?php $this->applyTemplateHook('single1-entity-info-mc-share-links','after') ?>
                        
                        <?php $this->applyTemplateHook('single1-entity-info-entity-admins','before') ?>
                        <entity-admins :entity="entity" classes="col-12"></entity-admins>
                        <?php $this->applyTemplateHook('single1-entity-info-entity-admins','after') ?>
                        
                        <?php $this->applyTemplateHook('single1-entity-info-entity-owner','before') ?>
                        <entity-owner classes="col-12"  title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                        <?php $this->applyTemplateHook('single1-entity-info-entity-owner','after') ?>

                    </div>
                </aside>
            </mc-container>
        </mc-tab>    
    </mc-tabs>   
    <entity-actions :entity="entity"></entity-actions>         
</div>