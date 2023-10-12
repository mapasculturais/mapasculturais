<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->layout = 'entity';

$this->addOpportunityPhasesToJs();

$this->import('
    mc-breadcrumb
    v1-embed-tool
    opportunity-header
    mc-link
    opportunity-phase-header
')
?>
<div class="main-app opportunity-registrations">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity">
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.parent || entity" route="single" hash="support" icon="arrow-left"><?= i::__('Voltar') ?></mc-link>
        </template>
    </opportunity-header>

    <div class="opportunity-registrations__container">
        <span class="title"> <?= i::__('Suporte') ?> </span>
        <opportunity-phase-header :phase="entity"></opportunity-phase-header>
        <v1-embed-tool route="sopportlist" :id="entity.id" min-height="600px"></v1-embed-tool>
    </div>
</div>