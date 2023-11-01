<?php
use MapasCulturais\i;
$this->layout = 'registrations';
$this->import('
    mc-breadcrumb
    mc-container
    opportunity-evaluations-list
    mc-alert
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

if (isset($this->controller->data['user']) && $entity->opportunity->canUser("@control")) {
    $userEvaluator = $app->repo("User")->find($this->controller->data['user']);
}else{
    $userEvaluator = $app->user;
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
                <opportunity-evaluations-list text-button="<?= i::__("Lista de avaliações") ?>" :entity="entity">
                    <v1-embed-tool route="sidebarleftevaluations" :id="entity.id"></v1-embed-tool>
                </opportunity-evaluations-list>
            </aside>
            
            <main class="col-6 grid-12">
                <?php if($entity->opportunity->evaluationMethod->slug === "documentary"):?>
                   <div class="col-12">
                       <mc-alert type="warning"><?= i::__('Para iniciar a de avaliação documental, selecione um campo de dados abaixo')?></mc-alert>
                   </div>
                <?php endif;?>
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
                            <v1-embed-tool route="registrationevaluationtionformview" iframe-id="evaluation-registration" :id="entity.id"></v1-embed-tool>
                        </div>
                    </div>
                </section>
            </main>

            <aside class="col-3">
                <div class="registration__right-sidebar">
                    <div class="registration__actions">
                        <h4 class="regular primary__color"><?= i::__("Formulário de") ?> <strong><?= $entity->opportunity->evaluationMethod->name ?></strong></h4>
                        <registration-evaluation-info :entity="entity"></registration-evaluation-info>
                            <?php if ($valuer_user) : ?>
                                <v1-embed-tool route="evaluationforms/uid:<?= $valuer_user->id ?>" iframe-id="evaluation-form" :id="entity.id"></v1-embed-tool>
                            <?php else : ?>
                                <v1-embed-tool route="evaluationforms" iframe-id="evaluation-form" :id="entity.id"></v1-embed-tool>
                            <?php endif ?>
                        </div>
                        <registration-evaluation-actions :registration="entity"></registration-evaluation-actions>
                    </div>
                </div>
            </aside>
        </div>
    </div>
</div>