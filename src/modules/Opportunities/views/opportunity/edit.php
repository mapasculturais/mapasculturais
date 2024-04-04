<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$entity = $this->controller->requestedEntity;
if(!$entity->isFirstPhase){
    $url = $app->createUrl("opportunity","edit",[$entity->firstPhase->id]);
    $app->redirect($url);
}

$this->layout = 'entity';

$this->import('
    entity-actions
    entity-header
    entity-links
    mc-breadcrumb
    mc-tab
    mc-tabs
    opportunity-basic-info
    opportunity-phase-reports
    opportunity-phases-config
    opportunity-subscribe-results
');

$this->addOpportunityPhasesToJs();

$label = $this->isRequestedEntityMine() ? i::__('Minhas oportunidades') : i::__('Oportunidades');
$this->breadcrumb = [
  ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
  ['label'=> $label, 'url' => $app->createUrl('panel', 'opportunities')],
  ['label'=> $entity->name, 'url' => $app->createUrl('opportunity', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mc-tabs class="tabs" sync-hash>
        <?php $this->applyTemplateHook('tabs','begin') ?>
        <mc-tab label="<?= i::__('Informações') ?>" slug="info">
            <opportunity-basic-info :entity="entity"></opportunity-basic-info>
        </mc-tab>
        <mc-tab label="<?= i::__('Configuração de fases') ?>" slug="config">
            <opportunity-phases-config :entity="entity" tabs='config'></opportunity-phases-config>
        </mc-tab>
        <mc-tab label="<?= i::__('Inscrições e Resultados') ?>" slug="registrations">
            <opportunity-subscribe-results :entity="entity" tab="registrations"></opportunity-subscribe-results>
        </mc-tab>
        <mc-tab label="<?= i::__('Relatórios') ?>" slug="report">
            <opportunity-phase-reports :entity="entity"></opportunity-phase-reports>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs','end') ?>
    </mc-tabs>
    
    <entity-actions :entity="entity" editable></entity-actions>
</div>
