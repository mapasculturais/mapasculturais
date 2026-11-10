<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 */

use MapasCulturais\i;

$this->layout = 'entity';
$this->addOpportunityBreadcramb(i::__('Lista de inscrições'), $entity);
$this->addOpportunityPhasesToJs($entity);
$this->import('
    entity-header
    entity-actions
    mc-breadcrumb
    mc-link
    opportunity-appeal-correction-assignment
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

        <opportunity-registrations-table identifier="registrationsList" :phase="entity"></opportunity-registrations-table>
    </div>

    <?php /* F1 (#17): modal de designação de correção (F2), aberto por evento global pela tabela acima; renderizado uma única vez na view (o init.php dele também injeta o contexto de @control/fase de recurso consumido pela coluna) */ ?>
    <opportunity-appeal-correction-assignment></opportunity-appeal-correction-assignment>
</div>