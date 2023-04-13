<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->layout = 'entity';
$this->addOpportunityBreadcramb(i::__('Lista de inscrições'));

$this->import('
    entity-header
    entity-actions
    mapas-breadcrumb
    mc-link
    opportunity-header
')
?>
<div class="main-app opportunity-registrations">
  <mapas-breadcrumb></mapas-breadcrumb>
  <opportunity-header :opportunity="entity.parent || entity">
    <template #button>
      <mc-link class="button button--primary-outline" :entity="entity.parent || entity" route="edit" hash="registrations" icon="arrow-left"><?= i::__('Voltar') ?></mc-link>
    </template>
  </opportunity-header>

  <div class="opportunity-registrations__container">
    <opportunity-phase-header :phase="entity"></opportunity-phase-header>
  </div>
</div>