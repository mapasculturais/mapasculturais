<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Entities\Registration $entity
 */

use MapasCulturais\i;

$this->layout = 'registrations';

$this->import('
    mc-breadcrumb
    opportunity-header
    support-edition
');

/* $breadcrumb = [
    ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => $opportunity->firstPhase->name, 'url' => $app->createUrl('opportunity', 'single', [$opportunity->firstPhase->id])],
]; */

/* $this->breadcrumb = $breadcrumb; */
?>

<div class="main-app support form">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <support-edition :registration="entity"></support-edition>
</div>