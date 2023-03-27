<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->breadcrumb = [
  ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
  ['label'=> i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label'=> $entity->name, 'url' => $app->createUrl('opportunity', 'formBuilder', [$entity->id])],
];

$this->import('
    entity-header
    entity-actions
    mapas-breadcrumb
    mc-link
    opportunity-form-builder
    opportunity-header
')
?>

<div class="main-app form-builder">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity">
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.parent || entity" route="edit" hash="config" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
        </template>
    </opportunity-header>

    <opportunity-form-builder :entity="entity.parent ? entity.parent : entity" :is-first-phase="entity.parent == null || entity.isFirstPhase"></opportunity-form-builder>

    <entity-actions :entity="entity.parent ? entity.parent : entity" editable></entity-actions>
</div>
