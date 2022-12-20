<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-actions
    entity-header
    entity-owner
    mapas-breadcrumb
    mapas-container
    tabs
');
$this->breadcramb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Selos'), 'url' => $app->createUrl('search', 'seals')],
    ['label' => $entity->name, 'url' => $app->createUrl('seal', 'single', [$entity->id])],
];
?>

<div class="main-app single">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity"></entity-header>
    <tabs class="tabs">
        <tab icon="exclamation" label="<?= i::_e('Informações gerais') ?>" slug="info">
            <div class="tabs__info">
                <mapas-container>
                    <main>
                        <div class="grid-12">
                            <div v-if="entity.shortDescription" class="col-12">
                                <h2><?php i::_e('Descrição');?></h2>
                                <p>{{entity.shortDescription}}</p>
                            </div>
                            <div v-if="entity.longDescription" class="col-12">
                                <h2><?php i::_e('Descrição Detalhada');?></h2>
                                <p>{{entity.longDescription}}</p>
                            </div>
                            <div v-if="entity.validPeriod" class="col-12">
                                <h2><?php i::_e('Período de Validade');?></h2>
                                <p>{{entity.validPeriod}}</p>
                            </div>
                        </div>
                    </main>
                    <aside>
                        <div class="grid-12">
                            <entity-owner classes="col-12"  title="<?php i::esc_attr_e('Publicado por');?>" :entity="entity"></entity-owner>                        
                        </div>
                    </aside>
                </mapas-container>
                <entity-actions :entity="entity"></entity-actions>                
            </div>
        </tab>
    </tabs>
</div>