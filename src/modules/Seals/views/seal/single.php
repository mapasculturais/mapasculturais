<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-actions
    entity-header
    entity-owner
    mc-breadcrumb
    mc-container
    entity-files-list
    entity-related-agents
    entity-links
    entity-request-ownership
    mc-tabs
    mc-tab
    mc-entities
    entity-card
    mc-title
    mc-icon
');
$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Meus Selos'), 'url' => $app->createUrl('panel', 'seals')],
    ['label' => $entity->name, 'url' => $app->createUrl('seal', 'single', [$entity->id])],
];
?>

<div class="main-app single">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity"></entity-header>

    <mc-container>
        <main>
            <div class="grid-12">

                <div class="entity-seals__validity col-12" v-if="entity.validPeriod" class="col-12">
                    <h2 class="entity-seals__validity--label"><?php i::_e('Validade do certificado do selo');?></h2>
                    
                    <p v-if="entity.validPeriod <= 12" class="entity-seals__validity--content">
                        {{ entity.validPeriod }} <?= i::__('Meses') ?>
                    </p>
                    
                    <p v-if="entity.validPeriod > 12" class="entity-seals__validity--content"> 
                        <template v-if="Math.floor(entity.validPeriod / 12) == 1">
                            {{ Math.floor(entity.validPeriod / 12)}} <?= i::__('ano') ?>
                        </template>

                        <template v-if="Math.floor(entity.validPeriod / 12) > 1">
                            {{ Math.floor(entity.validPeriod / 12)}} <?= i::__('anos') ?>
                        </template>

                        <?= i::__('e') ?>

                        <template v-if="(entity.validPeriod % 12) == 1">
                        {{(entity.validPeriod % 12)}} <?= i::__('mês') ?>
                        </template>

                        <template v-if="(entity.validPeriod % 12) > 1">
                        {{(entity.validPeriod % 12)}} <?= i::__('meses') ?>
                        </template>
                    </p>

                </div>

                <div v-if="entity.longDescription" class="col-12 grid-12">
                    <h2 class="col-12"><?php i::_e('Descrição');?></h2>
                    <p class="col-12" class="description" v-html="entity.longDescription"></p>
                </div>

                <entity-files-list :entity="entity" classes="col-12" group="downloads"  title="<?php i::esc_attr_e('Arquivos para download');?>"></entity-files-list>
                <entity-links :entity="entity" classes="col-12" title="<?php i::_e('Links'); ?>"></entity-links>
                
                <!-- Seção de entidades que possuem este selo -->
                <div class="col-12">
                    <h2><?php i::_e('Entidades com este selo'); ?></h2>
                    <mc-tabs class="seal-entities-tabs" sync-hash>
                        <!-- Agentes -->
                        <mc-tab label="<?php i::esc_attr_e('Agentes'); ?>" slug="agents">
                            <mc-entities 
                                type="agent" 
                                :query="{'@seals': entity.id}" 
                                select="id,name,files.avatar,shortDescription"
                                order="name ASC"
                                #default="{entities}">
                                <div v-if="entities.length > 0">
                                    <h3><?php i::_e('Agentes certificados'); ?></h3>
                                    <div class="grid-12">
                                        <div v-for="agent in entities" :key="agent.id" class="col-6 col-md-4 col-lg-3">
                                            <entity-card :entity="agent" tag="h4" portrait slice-description>
                                                <template #title>
                                                    <mc-title tag="h4" :shortLength="60">{{agent.name}}</mc-title>
                                                </template>
                                                <template #labels>
                                                    <div class="entityType agent__background">
                                                        <mc-icon :entity="agent"></mc-icon>
                                                        <?php i::_e('Agente'); ?>
                                                    </div>
                                                </template>
                                            </entity-card>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <p><?php i::_e('Nenhum agente possui este selo ainda.'); ?></p>
                                </div>
                            </mc-entities>
                        </mc-tab>
                        
                        <!-- Espaços -->
                        <mc-tab label="<?php i::esc_attr_e('Espaços'); ?>" slug="spaces">
                            <mc-entities 
                                type="space" 
                                :query="{'@seals': entity.id}" 
                                select="id,name,files.avatar,shortDescription"
                                order="name ASC"
                                #default="{entities}">
                                <div v-if="entities.length > 0">
                                    <h3><?php i::_e('Espaços certificados'); ?></h3>
                                    <div class="grid-12">
                                        <div v-for="space in entities" :key="space.id" class="col-6 col-md-4 col-lg-3">
                                            <entity-card :entity="space" tag="h4" portrait slice-description>
                                                <template #title>
                                                    <mc-title tag="h4" :shortLength="60">{{space.name}}</mc-title>
                                                </template>
                                                <template #labels>
                                                    <div class="entityType space__background">
                                                        <mc-icon :entity="space"></mc-icon>
                                                        <?php i::_e('Espaço'); ?>
                                                    </div>
                                                </template>
                                            </entity-card>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <p><?php i::_e('Nenhum espaço possui este selo ainda.'); ?></p>
                                </div>
                            </mc-entities>
                        </mc-tab>
                        
                        <!-- Eventos -->
                        <mc-tab label="<?php i::esc_attr_e('Eventos'); ?>" slug="events">
                            <mc-entities 
                                type="event" 
                                :query="{'@seals': entity.id}" 
                                select="id,name,files.avatar,shortDescription"
                                order="name ASC"
                                #default="{entities}">
                                <div v-if="entities.length > 0">
                                    <h3><?php i::_e('Eventos certificados'); ?></h3>
                                    <div class="grid-12">
                                        <div v-for="event in entities" :key="event.id" class="col-6 col-md-4 col-lg-3">
                                            <entity-card :entity="event" tag="h4" portrait slice-description>
                                                <template #title>
                                                    <mc-title tag="h4" :shortLength="60">{{event.name}}</mc-title>
                                                </template>
                                                <template #labels>
                                                    <div class="entityType event__background">
                                                        <mc-icon :entity="event"></mc-icon>
                                                        <?php i::_e('Evento'); ?>
                                                    </div>
                                                </template>
                                            </entity-card>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <p><?php i::_e('Nenhum evento possui este selo ainda.'); ?></p>
                                </div>
                            </mc-entities>
                        </mc-tab>
                        
                        <!-- Projetos -->
                        <mc-tab label="<?php i::esc_attr_e('Projetos'); ?>" slug="projects">
                            <mc-entities 
                                type="project" 
                                :query="{'@seals': entity.id}" 
                                select="id,name,files.avatar,shortDescription"
                                order="name ASC"
                                #default="{entities}">
                                <div v-if="entities.length > 0">
                                    <h3><?php i::_e('Projetos certificados'); ?></h3>
                                    <div class="grid-12">
                                        <div v-for="project in entities" :key="project.id" class="col-6 col-md-4 col-lg-3">
                                            <entity-card :entity="project" tag="h4" portrait slice-description>
                                                <template #title>
                                                    <mc-title tag="h4" :shortLength="60">{{project.name}}</mc-title>
                                                </template>
                                                <template #labels>
                                                    <div class="entityType project__background">
                                                        <mc-icon :entity="project"></mc-icon>
                                                        <?php i::_e('Projeto'); ?>
                                                    </div>
                                                </template>
                                            </entity-card>
                                        </div>
                                    </div>
                                </div>
                                <div v-else>
                                    <p><?php i::_e('Nenhum projeto possui este selo ainda.'); ?></p>
                                </div>
                            </mc-entities>
                        </mc-tab>
                    </mc-tabs>
                </div>
            </div>
        </main>
        <aside>
            <div class="grid-12">
                <entity-owner classes="col-12"  title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
            </div>
        </aside>
    </mc-container>

    <entity-actions :entity="entity"></entity-actions>
</div>