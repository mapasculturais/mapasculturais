<?php

/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Entities\Registration $entity
 */

use MapasCulturais\i;

$this->layout = 'entity';
$this->addOpportunityPhasesToJs();

$this->import('
    mc-breadcrumb
    opportunity-header
    opportunity-support-config
    mc-link
    opportunity-phase-header
')
?>
<div class="main-app opportunity-registrations">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity">
        <template #button>
            <mc-link class="button button--primary-outline button--icon" :entity="entity.parent || entity" route="edit" hash="support" icon="arrow-left"><?= i::__('Voltar') ?></mc-link>
        </template>
    </opportunity-header>

    <div class="opportunity-registrations__container opportunity-registrations__container--bound">
        <opportunity-phase-header :phase="entity"></opportunity-phase-header>
        <opportunity-support-config :entity="entity"><opportunity-support-config>
    </div>
</div>