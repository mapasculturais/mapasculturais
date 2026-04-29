<?php

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    entity-header
    entity-field
    entity-owner
    entity-status
    mc-breadcrumb
    mc-card
    mc-container
    mc-tabs
    mc-tab
    entity-actions
');

$label = i::__('Meus subsites');
$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => $label, 'url' => $app->createUrl('panel', 'subsites')],
    ['label' => $entity->name, 'url' => $app->createUrl('subsite', 'single', [$entity->id])],
];
?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    
    <mc-tabs class="tabs" sync-hash>
        <mc-tab label="<?= i::_e('Informações') ?>" slug="info">
            <mc-container>
                <entity-status :entity="entity"></entity-status>
                <mc-card>
                    <template #title>
                        <label><?php i::_e("Informações do Subsite") ?></label>
                        <p><?php i::_e("Dados básicos do subsite") ?></p>
                    </template>
                    <template #content>
                        <div class="grid-12">
                            <entity-field :entity="entity" disabled classes="col-12" prop="name" label="<?php i::_e('Nome') ?>"></entity-field>
                            <entity-field :entity="entity" disabled classes="col-12" prop="url" label="<?php i::_e('URL') ?>"></entity-field>
                            <entity-field :entity="entity" disabled classes="col-12" prop="aliasUrl" label="<?php i::_e('URL Alternativa') ?>"></entity-field>
                            <entity-field :entity="entity" disabled classes="col-12" prop="namespace" label="<?php i::_e('Namespace do Tema') ?>"></entity-field>
                        </div>
                    </template>
                </mc-card>
                
                <main>
                    <mc-card>
                        <template #title>
                            <h3 class="bold"><?php i::_e("Configurações") ?></h3>
                        </template>
                        <template #content>
                            <div class="grid-12">
                                <entity-field :entity="entity" disabled classes="col-12" prop="site_name" label="<?php i::_e('Nome do Site') ?>"></entity-field>
                                <entity-field :entity="entity" disabled classes="col-12" prop="site_description" label="<?php i::_e('Descrição do Site') ?>"></entity-field>
                                <entity-field :entity="entity" disabled classes="col-12" prop="color_primary" label="<?php i::_e('Cor Primária') ?>"></entity-field>
                                <entity-field :entity="entity" disabled classes="col-12" prop="color_secondary" label="<?php i::_e('Cor Secundária') ?>"></entity-field>
                            </div>
                        </template>
                    </mc-card>
                </main>
                
                <aside>
                    <mc-card>
                        <template #content>
                            <div class="grid-12">
                                <entity-owner :entity="entity" disabled classes="col-12" title="Publicado por"></entity-owner>
                            </div>
                        </template>
                    </mc-card>
                </aside>
            </mc-container>
        </mc-tab>
    </mc-tabs>
    <entity-actions :entity="entity"></entity-actions>
</div>
