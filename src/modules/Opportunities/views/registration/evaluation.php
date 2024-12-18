<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->layout = 'registrations';
$this->import('
    evaluation-form
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
    registration-info
    v1-embed-tool
');

$breadcrumb = [
    ['label' => i::__('Início'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Painel de controle'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Minhas Avaliações'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Lista de Avaliações'), 'url' => $app->createUrl('registration', 'index')],
];

$breadcrumb[] = ['label' => i::__('Formulário de avaliação')];


$this->breadcrumb = $breadcrumb;

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
                    <v1-embed-tool route="sidebarleftevaluations" :id="entity.id"></v1-embed-tool>
                </opportunity-evaluations-list>
            </aside>

            <main class="col-5 grid-12">
                <?php if ($entity->opportunity->evaluationMethod->slug === "documentary") : ?>
                    <div class="col-12">
                        <mc-alert type="warning"><?= i::__('Para iniciar a de avaliação documental, selecione um campo de dados abaixo') ?></mc-alert>
                    </div>
                <?php endif; ?>
                <mc-summary-agent :entity="entity" classes="col-12"></mc-summary-agent>
                <registration-info :registration="entity" classes="col-12"></registration-info>
                <mc-summary-agent-info :entity="entity" classes="col-12"></mc-summary-agent-info>
                <h3 class="col-12"><?= i::__('Dados informados no formulário') ?></h3>
                <mc-summary-spaces :entity="entity" classes="col-12"></mc-summary-spaces>
                <mc-summary-project :entity="entity" classes="col-12"></mc-summary-project>


                <section class="col-12 section">
                    <div class="col-12">
                        </div>
                        
                        <div class="section__content">
                            <div class="card owner">
                            <?php $this->applyTemplateHook("registration-evaluation-view", 'before', ['entity' => $entity]) ?>
                            <v1-embed-tool route="registrationevaluationtionformview" iframe-id="evaluation-registration" :id="entity.id"></v1-embed-tool>
                            <?php $this->applyTemplateHook("registration-evaluation-view", 'after', ['entity' => $entity]) ?>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="col-4">
                <div class="registration__right-sidebar">
                    <evaluation-form :entity="entity"></evaluation-form>
                </div>
            </aside>
        </div>
    </div>
</div>