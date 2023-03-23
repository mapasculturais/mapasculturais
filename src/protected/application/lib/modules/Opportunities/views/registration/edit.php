<?php

use MapasCulturais\i;

$this->layout = 'registrations';

$this->import('
    mapas-breadcrumb
    mapas-card
    mapas-container
    mc-icon
    opportunity-header
    registration-actions
    registration-related-agents
    registration-related-space
    registration-steps
    select-entity
    v1-embed-tool
');

$this->breadcrumb = [
    ['label' => i::__('Inicio'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Minhas oportunidades'), 'url' => $app->createUrl('panel', 'opportunity')],
    ['label' => $entity->name, 'url' => $app->createUrl('opportunity', 'single', [$entity->id])],
];

$stepsLabels = "['teste 1', 'teste 2', 'teste 3', 'teste 4', 'teste 5', 'teste 6', 'teste 7', 'teste 8', 'teste 9', 'teste 10', 'teste 11']";

/**
 * @todo registration-form
 */
?>

<div class="main-app registration edit">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <div class="registration__title">
        <?= i::__('Formulário de inscrição') ?>
    </div>

    <div class="registration__steps">
        <registration-steps></registration-steps>
    </div>

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
                            <p v-if="!entity.category" class="data__info">Sem categoria</p>
                        </div>
                    </div>
                </div>
                <section class="section">
                    <div class="section__title" id="main-info">
                        <?= i::__('Informações básicas') ?>
                    </div>
                    <div class="section__content">                         
                        <div class="card owner">                            
                            <div class="card__title"> 
                                <?= i::__('Agente responsável') ?> 
                            </div>
                            <div class="card__content">
                                <div class="owner">
                                    <div class="owner__image">
                                        <img v-if="entity.owner.files?.avatar" :src="entity.owner.files?.avatar?.transformations?.avatarSmall?.url" />
                                        <mc-icon v-if="!entity.owner.files?.avatar" name="image"></mc-icon>
                                    </div>
                                    <div class="owner__name">
                                        {{entity.owner.name}}
                                    </div>
                                </div>
                            </div>
                        </div>
                        <registration-related-agents :registration="entity"></registration-related-agents>
                        <registration-related-space :registration="entity"></registration-related-space>
                    </div>
                </section>

                <section class="section">
                    <v1-embed-tool iframe-id="registration-form" route="registrationform" :id="entity.id"></v1-embed-tool>
                </section>
            </main>

            <aside>
                <registration-actions :registration="entity"></registration-actions>
            </aside>
        </mapas-container>
    </div>
</div>