<?php
use MapasCulturais\i;
$this->layout = 'entity';

$breadcrumb = [
  ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
  ['label'=> i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label'=> $entity->firstPhase->name, 'url' => $app->createUrl('opportunity', 'edit', [$entity->firstPhase->id])]
];

if ($entity->isFirstPhase) {
    $breadcrumb[] = ['label'=> i::__('Período de inscrição')];
} else {
    $breadcrumb[] = ['label'=> $entity->name];
}
$breadcrumb[] = ['label'=> i::__('Configuração do formulário'), 'url' => $app->createUrl('opportunity', 'formBuilder', [$entity->id])];

$this->breadcrumb = $breadcrumb;

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
    <opportunity-header :opportunity="entity.parent || entity">
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.parent || entity" route="edit" hash="config" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
        </template>
    </opportunity-header>

    <opportunity-form-builder :entity="entity"></opportunity-form-builder>

    <entity-actions :entity="entity" editable :can-delete="false"></entity-actions>
</div>
