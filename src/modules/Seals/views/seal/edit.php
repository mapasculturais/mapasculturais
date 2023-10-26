<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->import('
    entity-actions
    entity-admins
    entity-cover
    entity-field
    entity-header
    entity-owner
    entity-parent-edit
    entity-profile
    entity-related-agents
    mc-breadcrumb
    mc-card
    mc-container
    mc-tab
    mc-tabs
    seal-form-information-seal
    seal-locked-field
');

$this->breadcrumb = [
    ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label'=> i::__('Meus Selos'), 'url' => $app->createUrl('panel', 'seals')],
    ['label'=> $entity->name, 'url' => $app->createUrl('seal', 'edit', [$entity->id])],
];
?>

<div class="main-app">
  <mc-breadcrumb></mc-breadcrumb>
  <entity-header :entity="entity"></entity-header>
    <mc-tabs class="tabs tabs-seal-edit">
        <mc-tab label="<?= i::__('Informações gerais') ?>" slug="info">
            <div class="tabs__info">
                <mc-container>
                    <main>
                        <seal-form-information-seal :entity="entity"></seal-form-information-seal>
                    </main>
                    <aside>
                        <mc-card>
                            <template #content>
                                <div class="grid-12">
                                    <entity-admins :entity="entity" classes="col-12" editable></entity-admins>
                                    <entity-owner :entity="entity" classes="col-12" title="<?php i::_e('Publicado por')?>" editable></entity-owner>
                                    <entity-parent-edit :entity="entity" classes="col-12" type="seal" ></entity-parent-edit>
                                    <entity-related-agents :entity="entity" classes="col-12" editable></entity-related-agents>
                                </div>
                            </template>
                        </mc-card>
                    </aside>
                </mc-container>
            </div>
        </mc-tab>
        <mc-tab label="<?= i::__('Bloqueio de campos') ?>" slug="info_block">
            <div class="tabs__info">
                <seal-locked-field classes="tabs-seal-edit" :entity="entity"></seal-locked-field>
            </div>
        </mc-tab>
    </mc-tabs>

  <entity-actions :entity="entity" editable></entity-actions>
</div>