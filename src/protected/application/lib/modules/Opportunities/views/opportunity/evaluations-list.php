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
    mapas-breadcrumb
    mc-link
    opportunity-evaluations-table
    opportunity-header
    opportunity-phase-header
')
?>
<div class="main-app opportunity-evaluations">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity">
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.opportunity" route="edit" hash="registrations" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
        </template>
    </opportunity-header>

    <div class="opportunity-registrations__container">
        <opportunity-phase-header :phase="entity"></opportunity-phase-header>
        <opportunity-evaluations-table :phase="entity"></opportunity-evaluations-table>
    </div>
</div>