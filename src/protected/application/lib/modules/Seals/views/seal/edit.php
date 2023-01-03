<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-actions
    entity-admins
    entity-cover
    entity-field
    entity-header
    entity-owner
    entity-profile
    mapas-breadcrumb
    mapas-card
    mapas-container
    form-valid-period
    form-block-fields
    form-information-seal
    tabs
');

$this->breadcramb = [
  ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
  ['label'=> i::__('Meus Selos'), 'url' => $app->createUrl('panel', 'seals')],
  ['label'=> $entity->name, 'url' => $app->createUrl('seal', 'edit', [$entity->id])],
];
?>

<div class="main-app">
  <mapas-breadcrumb></mapas-breadcrumb>
  <entity-header :entity="entity" editable></entity-header>
    <tabs class="tabs">
        <tab label="<?= i::_e('Informações gerais') ?>" slug="info">
            <div class="tabs__info">
                <mapas-container>
                    <main>
                        <form-information-seal :entity="entity"></form-information-seal>
                    </main>
                    <aside>
                        <mapas-card>
                            <template #content>
                                <div class="grid-12">
                                    <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                                    <entity-owner :entity="entity" classes="col-12" title="<?php i::_e('Publicado por')?>" editable></entity-owner>
                                    <entity-parent-edit :entity="entity" classes="col-12" type="project" ></entity-parent-edit>
                                </div>
                            </template>
                        </mapas-card>
                    </aside>
                </mapas-container>
            </div>
        </tab>
        <tab label="<?= i::_e('Bloqueio de campos') ?>" slug="info_block">
            <div class="tabs__info">
                <mapas-container>
                    <mapas-card class="feature">
                        <form-block-fields classes="col-12" :entity="entity"></form-block-fields>
                    </mapas-card>
                </mapas-container>
            </div>
        </tab>
    </tabs>

  <entity-actions :entity="entity" editable></entity-actions>
</div>