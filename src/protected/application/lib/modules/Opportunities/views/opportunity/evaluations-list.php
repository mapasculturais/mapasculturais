<?php
use MapasCulturais\i;
$this->layout = 'entity';
$this->addOpportunityBreadcramb(i::__('Lista de avaliações'));

$this->import('
    entity-header
    entity-actions
    mapas-breadcrumb
    mc-link
    opportunity-evaluations
    opportunity-header
')
?>
<div class="main-app opportunity-evaluations">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity">
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.opportunity" route="edit" hash="registrations" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
        </template>
    </opportunity-header>

    <opportunity-evaluations :entity="entity"></opportunity-evaluations>

</div>
