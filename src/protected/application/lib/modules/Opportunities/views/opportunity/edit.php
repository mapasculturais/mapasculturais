<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->import('
    entity-actions
    entity-header
    entity-links
    mc-breadcrumb
    mc-tabs
    opportunity-basic-info
    opportunity-phase-reports
    opportunity-phases-config
    opportunity-subscribe-results
');

$this->addOpportunityPhasesToJs();

$this->breadcrumb = [
  ['label'=> i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
  ['label'=> i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
  ['label'=> $entity->name, 'url' => $app->createUrl('opportunity', 'edit', [$entity->id])],
];
?>

<div class="main-app">
    <mc-breadcrumb></mc-breadcrumb>
    <entity-header :entity="entity" editable></entity-header>
    <mc-tabs class="tabs">
        <tab label="<?= i::__('Informações') ?>" slug="info">
            <opportunity-basic-info :entity="entity"></opportunity-basic-info>
        </tab>
        <tab label="<?= i::__('Configuração de fases') ?>" slug="config">
            <opportunity-phases-config :entity="entity"></opportunity-phases-config>
        </tab>
        <tab label="<?= i::__('Inscrições e Resultados') ?>" slug="registrations">
            <opportunity-subscribe-results :entity="entity"></opportunity-subscribe-results>
        </tab>
        <tab label="<?= i::__('Relatórios') ?>" slug="report">
            <opportunity-phase-reports :entity="entity"></opportunity-phase-reports>
        </tab>
    </mc-tabs>
    <entity-actions :entity="entity" editable></entity-actions>
</div>
