<?php

use MapasCulturais\i;

$this->layout = 'registrations';

$this->import('
    mapas-breadcrumb
    mapas-card
    mapas-container
    mc-icon
    mc-side-menu
    opportunity-header
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
?>

<div class="main-app registration edit">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity">
        <template #footer>
            <mc-summary-evaluate></mc-summary-evaluate>
            <?php if(!$app->user->is('admin')): ?>
                <mc-side-menu text-button="<?= i::__("Lista de avaliações") ?>" :entity="entity">
                    <v1-embed-tool route="sidebarleftevaluations" :id="entity.id"></v1-embed-tool>
                </mc-side-menu>
            <?php endif ?>
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

                <section class="section">
                    <div class="section__content">
                        <div class="card owner">
                            <div class="card__title">
                                <?= i::__('Dados do proponente') ?>
                            </div>
                            <p><strong><?= i::__("Nome:") ?></strong> <?= i::__("Lorem ipsum ipsum") ?></p>
                            <p><strong><?= i::__("Descrição curta:") ?></strong> <?= i::__("Est nesciunt excepturi et laborum exercitationem aut dolor veritatis et omnis velit.") ?></p>
                            <p><strong><?= i::__("CPF ou CNPJ::") ?></strong> <?= i::__("642.653.000-06") ?></p>
                            <p><strong><?= i::__("Email:") ?></strong> <?= i::__("emaildecontato@email.com.br") ?></p>
                            <p><strong><?= i::__("Raça:") ?></strong> <?= i::__("Informação oculta") ?></p>
                            <p><strong><?= i::__("Gênero:") ?></strong> <?= i::__("Informação oculta") ?></p>
                            <p><strong><?= i::__("Endereço:") ?></strong> <?= i::__("Rua dos Protótipos, 85, Cidade Fícticia, Ceará, Brasil") ?></p>
                            <p><strong><?= i::__("CEP:") ?></strong> <?= i::__("18619-408") ?></p>
                        </div>
                    </div>
                </section>

                <div class="col-12 registration-info">
                    <p class="registration-info__title"><?= i::__('Dados informados no formulário') ?></p>
                    <div class="registration-info__content">
                        <registration-related-space :registration="entity"></registration-related-space>
                    </div>
                </div>

                <section class="section">
                    <div class="section__content">
                        <div class="card owner">
                            <v1-embed-tool route="registrationevaluationtionformview" :id="entity.id"></v1-embed-tool>
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
                            <v1-embed-tool route="evaluationforms/uid:<?= $valuer_user->id ?>" :id="entity.id"></v1-embed-tool>
                        <?php else : ?>
                            <v1-embed-tool route="evaluationforms" :id="entity.id"></v1-embed-tool>
                        <?php endif ?>

                    </div>
                    <registration-evaluation-actions :registration="entity"></registration-evaluation-actions>
                </div>
            </aside>
        </mapas-container>
    </div>
</div>