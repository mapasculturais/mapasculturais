<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->layout = 'entity';
$this->addOpportunityBreadcramb(i::__('Lista de inscrições'));
$this->addOpportunityPhasesToJs();
$this->import('
    entity-header
    entity-actions
    mc-breadcrumb
    mc-link
    opportunity-form-builder
    opportunity-header
    opportunity-registrations-table
    v1-embed-tool
')
?>
<div class="main-app opportunity-registrations">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.parent || entity">
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.parent || entity" route="edit" hash="registrations" icon="arrow-left"><?= i::__('Voltar') ?></mc-link>
        </template>
    </opportunity-header>

    <div class="opportunity-registrations__container">
        <opportunity-phase-header :phase="entity"></opportunity-phase-header>

        <opportunity-registrations-table :phase="entity"></opportunity-registrations-table>
    </div>
</div>