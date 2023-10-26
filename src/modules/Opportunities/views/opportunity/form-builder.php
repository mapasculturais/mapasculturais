<?php
use MapasCulturais\i;
$this->layout = 'entity';

$this->addOpportunityBreadcramb(i::__('Configuração do formulário'));

$this->import('
    entity-actions
    mc-breadcrumb
    mc-link
    opportunity-form-builder
    opportunity-header
')
?>

<div class="main-app form-builder">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.parent || entity">
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.parent || entity" route="edit" hash="config" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
        </template>
    </opportunity-header>

    <opportunity-form-builder :entity="entity"></opportunity-form-builder>

    <entity-actions :entity="entity" editable :can-delete="false"></entity-actions>
</div>
