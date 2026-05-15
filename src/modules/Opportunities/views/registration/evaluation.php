<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'registrations';
$this->import('
    evaluation-form
    appeal-previous-evaluation-results
    mc-alert
    mc-breadcrumb
    mc-container
    mc-summary-agent
    mc-summary-agent-info
    mc-summary-evaluate
    mc-summary-project
    mc-summary-spaces
    opportunity-evaluations-list
    opportunity-header
    registration-evaluation-actions
    registration-evaluation-info
    registration-field-view
    registration-info
    registration-workplan-form
');

$referer = $app->request->getReferer();

$breadcrumb = [
    ['label' => i::__('Início'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Painel de controle'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Minhas Avaliações'), 'url' => $app->createUrl('panel', 'evaluations')],
    ['label' => i::__('Lista de Avaliações'), 'url' => $referer],
];

$breadcrumb[] = ['label' => i::__('Formulário de avaliação')];

$this->breadcrumb = $breadcrumb;

if ($entity->opportunity->isAppealPhase) {
    $parent_registration = $app->repo('registration')->findOneBy([
        'owner' => $entity->owner->id,
        'opportunity' => $entity->opportunity->parent->id,
        'number' => $entity->number
    ]);
}

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $userEvaluator = $app->repo("User")->find($this->controller->data['user']);
} else {
    $userEvaluator = $app->user;
}
?>

<div class="main-app registration edit">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity">
        <template #title-name>
            <span class="title__title">
                <a :href="entity.opportunity.getUrl('userEvaluations')">{{entity.opportunity.name}}</a>
            </span>
        </template>
        <template #button>
            <mc-link class="button button--primary-outline" :entity="entity.opportunity" route="userEvaluations" icon="arrow-left"><?= i::__("Voltar") ?></mc-link>
        </template>
        <template #footer>
            <mc-summary-evaluate></mc-summary-evaluate>
        </template>
        <template v-if="entity.opportunity.currentUserPermissions['@control']" #opportunity-header-info-end>
            <h4><?= i::__('Avaliador: ') ?><?= $userEvaluator->profile->name ?: "" ?></h4>
        </template>
    </opportunity-header>

    <div class="registration__content">
        <div class="grid-12 registration__grid">

            <aside class="col-3">
                <opportunity-evaluations-list text-button="<?= i::__("Lista de avaliações") ?>" :entity="entity" user-evaluator-id="<?=$userEvaluator->id?>">
                </opportunity-evaluations-list>
            </aside>

            <main class="col-5">
                <div class="grid-12 v-top">
                    <?php if ($entity->opportunity->evaluationMethod->slug === "documentary") : ?>
                        <div class="col-12">
                            <mc-alert type="warning"><?= i::__('Para iniciar a de avaliação documental, selecione um campo de dados abaixo') ?></mc-alert>
                        </div>
                    <?php endif; ?>
                    <mc-summary-agent :entity="entity" classes="col-12"></mc-summary-agent>
                    <registration-info :registration="entity" classes="col-12"></registration-info>
                    <mc-summary-agent-info :entity="entity" classes="col-12"></mc-summary-agent-info>

                    <appeal-previous-evaluation-results></appeal-previous-evaluation-results>

                    <!-- Caso seja uma fase de recurso -->
                    <section v-if="entity.opportunity?.isAppealPhase" class="col-12 grid-12 section">
                        <h3 class="col-12"><?= i::__('Recurso') ?></h3>

                        <div class="section__content col-12">
                            <div class="card owner">
                                <?php $this->applyTemplateHook("registration-appealPhase-evaluation-view", 'before', ['entity' => $entity]) ?>
                                    <registration-field-view :registration="entity" :phase-id="entity.id"></registration-field-view>
                                <?php $this->applyTemplateHook("registration-appealPhase-evaluation-view", 'after', ['entity' => $entity]) ?>
                            </div>
                        </div>
                    </section>


                    <section class="col-12  grid-12 section">
                        <h3 class="col-12"><?= i::__('Dados informados no formulário') ?></h3>
                        <mc-summary-spaces :entity="entity" classes="col-12"></mc-summary-spaces>
                        <mc-summary-project :entity="entity" classes="col-12"></mc-summary-project>

                        <div class="section__content col-12">
                            <div class="card owner">
                            <?php $this->applyTemplateHook("registration-evaluation-view", 'before', ['entity' => $entity]) ?>
                                <?php if ($entity->opportunity->isAppealPhase): ?>
                                    <registration-field-view :registration="entity" :phase-id="<?= (int) $parent_registration->id ?>"></registration-field-view>
                                <?php else: ?>
                                    <registration-field-view :registration="entity" :phase-id="entity.id"></registration-field-view>
                                <?php endif; ?>
                            <?php $this->applyTemplateHook("registration-evaluation-view", 'after', ['entity' => $entity]) ?>
                            </div>

                            <?php if ($entity->opportunity->isReportingPhase && $entity->opportunity->parent->enableWorkplan): ?>
                                <registration-workplan-form :phase-id="<?= $entity->opportunity->id ?>"></registration-workplan-form>
                            <?php endif; ?>
                        </div>
                    </section>
                </div>
            </main>

            <aside class="col-4">
                <div class="registration__right-sidebar">
                    <evaluation-form :entity="entity"></evaluation-form>
                </div>
            </aside>
        </div>
    </div>
</div>
