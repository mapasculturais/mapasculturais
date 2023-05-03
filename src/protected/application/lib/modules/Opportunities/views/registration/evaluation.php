<?php

use MapasCulturais\i;

$this->layout = 'registrations';

$this->import('
    mapas-breadcrumb
    mapas-card
    mapas-container
    mc-icon
    mc-side-menu
    mc-summary-spaces
    mc-summary-project
    opportunity-header
    mc-summary-agents
    mc-summary-evaluate
    v1-embed-tool 
    registration-evaluation-actions
    registration-related-agents
    registration-related-space
    registration-related-project
    registration-steps
    select-entity
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
if(isset($this->controller->data['user']) && $app->user->is('admin')){
    $userEvaluator = $app->repo("User")->find($this->controller->data['user']);
}
?>

<div class="main-app registration edit">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity">
        <template #footer>
            <mc-summary-evaluate></mc-summary-evaluate>
            <mc-side-menu v-if="entity.opportunity.currentUserPermissions['@control']" text-button="<?= i::__("Lista de avaliações") ?>" :entity="entity">
                <v1-embed-tool route="sidebarleftevaluations" :id="entity.id"></v1-embed-tool>
            </mc-side-menu>
        </template>  
        <template v-if="entity.opportunity.currentUserPermissions['@control']" #opportunity-header-info-end>
                <h4><?= i::__('Avaliador: ') ?><?= $userEvaluator->profile->name ?? "" ?></h4>
        </template>

    </opportunity-header>
    <div class="registration__content">

        <mapas-container>
            <main class="grid-12">
                <div class="col-12 registration-info">
                    <p class="registration-info__title"> <?= i::__('Informações da inscrição') ?> </p>
                    <div class="registration-info__content">
                        <div class="data">
                            <p class="data__title"> <?= i::__('Inscrição') ?> </p>
                            <p class="data__info">{{entity.number}}</p>
                        </div>
                        <div class="data">
                            <p class="data__title"> <?= i::__('Data') ?> </p>
                            <p class="data__info">{{entity.createTimestamp.date('2-digit year')}}</p>
                        </div>
                        <div class="data">
                            <p class="data__title"> <?= i::__('Categoria') ?> </p>
                            <p v-if="entity.category" class="data__info">{{entity.category}}</p>
                            <p v-if="!entity.category" class="data__info"><?php i::_e('Sem categoria') ?></p>
                        </div>
                    </div>
                </div>
                
                <mc-summary-project :entity="entity"></mc-summary-project>
                <mc-summary-agents :entity="entity"></mc-summary-agents>
                <mc-summary-spaces :entity="entity"></mc-summary-spaces>
                
                <section class="section">
                    <p class="registration-info__title"><?= i::__('Dados informados no formulário') ?></p>
                    <div class="section__content">
                        <div class="card owner">
                            <v1-embed-tool route="registrationevaluationtionformview" iframe-id="evaluation-registration" :id="entity.id"></v1-embed-tool>
                        </div>
                    </div>
                </section>
            </main>

            <aside>
                <div class="registration-evaluation-actions">
                    <div class="registration-evaluation-actions__form">

                        <div class="registration-evaluation-actions__form--title">
                            <p><?= i::__("Formulário de") ?> <strong><?= $entity->opportunity->evaluationMethodConfiguration->type->name ?></strong></p>
                        </div>
                        <?php if ($valuer_user) : ?>
                            <v1-embed-tool route="evaluationforms/uid:<?= $valuer_user->id ?>" iframe-id="evaluation-form" :id="entity.id"></v1-embed-tool>
                        <?php else : ?>
                            <v1-embed-tool route="evaluationforms" iframe-id="evaluation-form" :id="entity.id"></v1-embed-tool>
                        <?php endif ?>

                    </div>
                    <registration-evaluation-actions :registration="entity"></registration-evaluation-actions>
                </div>
            </aside>
        </mapas-container>
    </div>
</div>