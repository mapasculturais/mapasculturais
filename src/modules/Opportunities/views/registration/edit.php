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
    mc-card
    mc-container
    mc-icon
    opportunity-header
    registration-actions
    registration-form
    request-agent-avatar 
    registration-related-agents
    registration-related-space
    registration-related-project
    registration-steps
    select-entity
');

$this->useOpportunityAPI();

$opportunity = $entity->opportunity;

$breadcrumb = [
    ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => $opportunity->firstPhase->name, 'url' => $app->createUrl('opportunity', 'single', [$opportunity->firstPhase->id])],
];

if (!$opportunity->isFirstPhase) {
    $breadcrumb[] = ['label' => $opportunity->name, 'url' => $app->createUrl('opportunity', 'single', [$opportunity->id])];
}

$breadcrumb[] = ['label' => i::__('Formulário')];

$this->breadcrumb = $breadcrumb;

/**
 * @todo registration-form
 */

 $this->import('
    entity-field
    entity-renew-lock
    mc-avatar
    opportunity-header
    registration-autosave-notification
    registration-info
    registration-quotas-card
    registration-steps
');
?>

<div class="main-app registration edit">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <div class="registration__title">
        <h1>
            <?= i::__('Formulário de inscrição') ?>
        </h1>
        <h3>
            <?= $opportunity->name ?>
        </h3>
    </div>

    <div class="registration__content">
        <div class="registration__steps">
            <registration-steps></registration-steps>
        </div>

        <mc-container>
            <main class="grid-12">
                <registration-info :registration="entity" classes="col-12"></registration-info>                
                
                <section class="section">
                    <h2 class="section__title" id="main-info">
                        <?= i::__('Informações básicas') ?>
                    </h2>
                    <registration-autosave-notification :registration="entity"></registration-autosave-notification>

                    <div class="section__content">                         
                        <div class="card owner">                            
                            <div class="card__content">
                                <div class="owner">
                                    <mc-avatar v-if="!entity.opportunity.requestAgentAvatar" :entity="entity.owner" size="small"></mc-avatar>
                                    <request-agent-avatar v-if="entity.opportunity.requestAgentAvatar" :entity="entity"></request-agent-avatar>
                                    <div class="owner__content">
                                        <div class="owner__content--title">
                                            <h3 class="card__title"> 
                                                <?= i::__('Agente responsável') ?> 
                                            </h3>
                                            <div class="owner__name">
                                                {{entity.owner.name}}
                                            </div>
                                        </div>
                                        <div v-if="entity.opportunity.requestAgentAvatar" class="card__mandatory"> 
                                            <div class="obrigatory"> <?= i::__('*obrigatório') ?> </div>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <registration-quotas-card :entity="entity" v-if="entity.opportunity.enableQuotasQuestion"></registration-quotas-card>

                        <registration-related-agents :registration="entity"></registration-related-agents>
                        <registration-related-space :registration="entity"></registration-related-space>
                        <registration-related-project :registration="entity"></registration-related-project>
                    </div>
                </section>

                <section class="section">
                    <registration-form :registration="entity"></registration-form>
                </section>
            </main>

            <aside>
                <registration-actions :registration="entity"></registration-actions>
            </aside>
        </mc-container>
    </div>
</div>