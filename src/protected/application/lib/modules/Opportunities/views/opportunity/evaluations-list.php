<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->layout = 'entity';
$this->addOpportunityBreadcramb(i::__('Lista de avaliações'));
$this->addOpportunityPhasesToJs();

$this->import('
    entity-actions
    entity-header
    mc-breadcrumb
    mc-link
    mc-summary-evaluate
    opportunity-evaluations-table
    opportunity-header
    opportunity-phase-header
')
?>
<div class="main-app opportunity-evaluations">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity">
        <template #button>
            <mc-link v-if="entity.currentUserPermissions.modify" class="button button--primary-outline" :entity="entity.opportunity" route="edit" hash="registrations" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
            <mc-link v-if="!entity.currentUserPermissions.modify" class="button button--primary-outline" route="panel/index" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
        </template>
        <template #opportunity-header-info-end>
        </template>
    </opportunity-header>

    <div class="opportunity-registrations__container">
        <opportunity-phase-header :phase="entity"></opportunity-phase-header>
        <mc-summary-evaluate></mc-summary-evaluate>
        <opportunity-evaluations-table :phase="entity"></opportunity-evaluations-table>
    </div>
</div>