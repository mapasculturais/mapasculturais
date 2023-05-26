<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Entities\Registration $entity
 */

use MapasCulturais\i;

$this->layout = 'registrations';

$this->import('
    mapas-breadcrumb
    mc-card
    mapas-container
    mc-icon
    opportunity-header
    registration-actions
    registration-related-agents
    registration-related-space
    registration-related-project
    registration-steps
    select-entity
    v1-embed-tool
');

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
?>

<div class="main-app registration edit">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <div class="registration__title">
        <?= i::__('Formulário de inscrição') ?>
    </div>

    <div class="registration__content">
        <div class="registration__steps">
            <registration-steps></registration-steps>
        </div>

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
                        <registration-related-project :registration="entity"></registration-related-project>
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