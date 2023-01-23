<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-header
    mapas-breadcrumb
');
$this->breadcrumb = [
  ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
  ['label' => i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
  ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
];
?>

<div class="main-app single">
  <mapas-breadcrumb></mapas-breadcrumb>
  <entity-header :entity="entity"></entity-header>
</div>
