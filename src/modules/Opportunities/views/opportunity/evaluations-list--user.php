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
            <mc-link v-if="entity.currentUserPermissions.modify" 
                    :entity="entity.opportunity.parent || entity.opportunity" 
                    route="single" 
                    hash="evaluations" 
                    icon="arrow-left"
                    class="button button--primary-outline"><?= i::__("Voltar") ?></mc-link>
        </template>
        <template #opportunity-header-info-end>
        </template>
    </opportunity-header>

    <div class="opportunity-registrations__container grid-12">
        <opportunity-phase-header :phase="entity" classes="col-12"></opportunity-phase-header>
        <mc-summary-evaluate classes="col-12"></mc-summary-evaluate>
        <opportunity-evaluations-table :phase="entity" user="<?= $valuer_user->id ?>" identifier="userEvaluationsTable" classes="col-12"></opportunity-evaluations-table>
    </div>
</div>