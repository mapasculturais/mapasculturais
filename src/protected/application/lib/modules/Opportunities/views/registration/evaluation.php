<?php
use MapasCulturais\i;
$this->layout = 'registrations';
$this->import('
    mc-breadcrumb
    mc-container
    mc-side-menu
    mc-summary-agent
    mc-summary-agent-info
    mc-summary-evaluate
    mc-summary-project
    mc-summary-spaces
    opportunity-header
    registration-evaluation-actions
    registration-info
    v1-embed-tool
    registration-evaluation-info
');

$opportunity = $entity->opportunity;

$breadcrumb = [
    ['label' => i::__('Início'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Painel de controle'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Minhas Avaliações'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => i::__('Lista de Avaliações'), 'url' => $app->createUrl('registration', 'index')],
];

$breadcrumb[] = ['label' => i::__('Formulário de avaliação')];


$this->breadcrumb = $breadcrumb;
$userEvaluator = null;
if (isset($this->controller->data['user']) && $app->user->is('admin')) {
    $userEvaluator = $app->repo("User")->find($this->controller->data['user']);
}
?>

<div class="main-app registration edit">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity">
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
                <div>
                    <mc-side-menu text-button="<?= i::__("Lista de avaliações") ?>" :entity="entity">
                        <v1-embed-tool route="sidebarleftevaluations" :id="entity.id"></v1-embed-tool>
                    </mc-side-menu>
                </div>
            </aside>

            <main class="col-6">

                <h3 class="section__title"><?= i::__('Dados informados no formulário') ?></h3>
                <mc-summary-agent :entity="entity"></mc-summary-agent>
                <mc-summary-project :entity="entity"></mc-summary-project>
                <mc-summary-agent-info :entity="entity"></mc-summary-agent-info>
                <mc-summary-spaces :entity="entity"></mc-summary-spaces>

                <registration-info :registration="entity"></registration-info>

                <section class="section">
                    <div class="section__content">
                        <div class="card owner">
                            <v1-embed-tool route="registrationevaluationtionformview" iframe-id="evaluation-registration" :id="entity.id"></v1-embed-tool>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="col-3">
                <div class="registration__actions">
                    <h3 class="regular primary__color"><?= i::__("Formulário de") ?> <strong><?= $entity->opportunity->evaluationMethodConfiguration->type->name ?></strong></h3>

                    <registration-evaluation-info :entity="entity"></registration-evaluation-info>

                    <?php if ($valuer_user) : ?>
                        <v1-embed-tool route="evaluationforms/uid:<?= $valuer_user->id ?>" iframe-id="evaluation-form" :id="entity.id"></v1-embed-tool>
                    <?php else : ?>
                        <v1-embed-tool route="evaluationforms" iframe-id="evaluation-form" :id="entity.id"></v1-embed-tool>
                    <?php endif ?>
                </div>

                <registration-evaluation-actions :registration="entity"></registration-evaluation-actions>
            </aside>
        </div>
    </div>
</div>