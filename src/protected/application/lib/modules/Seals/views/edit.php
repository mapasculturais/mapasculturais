<?php 
use MapasCulturais\i;
$this->layout = 'entity'; 

$this->import('
    entity-actions
    entity-field
    entity-header
    entity-owner
    mapas-breadcrumb
    mapas-card
    mapas-container
');

$this->breadcramb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Selos'), 'url' => $app->createUrl('panel', 'seal')],
    ['label'=> $entity->name, 'url' => $app->createUrl('seal', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <mapas-breadcrumb></mapas-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mapas-container>
        <mapas-card class="feature">
            <template #title>
                <label><?php i::_e("Informações de selos")?></label>
                <p><?php i::_e("Texto exemplo de texto")?></p>
            </template>
            <template #content>
                <div class="left">
                    <div class="grid-12 v-bottom">
                        <entity-field :entity="entity" classes="col-9 sm:col-12" prop="name"></entity-field>
                        <entity-field :entity="entity" classes="col-12" prop="shortDescription"></entity-field>
                    </div>                      
                </div>
            </template>
        </mapas-card>
        <aside>
            <mapas-card>
                <template #content>
                    <div class="grid-12">
                        <entity-owner :entity="entity" classes="col-12" title="Publicado por" editable></entity-owner>
                    </div>
                </template>
            </mapas-card>
        </aside>
    </mapas-container>
    <entity-actions :entity="entity" editable></entity-actions>
</div>
