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

    <mc-tabs class="tabs" sync-hash>
        <mc-tab icon="exclamation" label="<?= i::_e('Informações') ?>" slug="info">
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
                    </div>
                </main>
                <aside>
                    <div class="grid-12">
                        <entity-owner classes="col-12"  title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>
                        <entity-related-agents :entity="entity" classes="col-12" title="<?php i::esc_attr_e('Agentes Relacionados');?>"></entity-related-agents>
                    </div>
                </aside>
            </mc-container>
        </mc-tab>    
    </mc-tabs>  

    <entity-actions :entity="entity"></entity-actions>
</div>