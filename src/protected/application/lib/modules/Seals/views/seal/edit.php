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
    entity-related-agents
    entity-parent-edit
');

$this->breadcrumb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Selos'), 'url' => $app->createUrl('panel', 'seals')],
    ['label'=> $entity->name, 'url' => $app->createUrl('seal', 'edit', [$entity->id])],
];
?>

<div class="main-app">
  <mapas-breadcrumb></mapas-breadcrumb>
  <entity-header :entity="entity"></entity-header>
    <tabs class="tabs tabs-seal-edit">
        <tab label="<?= i::__('Informações gerais') ?>" slug="info">
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
                                    <entity-parent-edit :entity="entity" classes="col-12" type="seal" ></entity-parent-edit>
                                    <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                                </div>
                            </template>
                        </mapas-card>
                    </aside>
                </mapas-container>
            </div>
        </tab>
        <tab label="<?= i::__('Bloqueio de campos') ?>" slug="info_block">
            <div class="tabs__info">
                <form-block-fields classes="tabs-seal-edit" :entity="entity"></form-block-fields>
            </div>
        </tab>
    </tabs>

  <entity-actions :entity="entity" editable></entity-actions>
</div>