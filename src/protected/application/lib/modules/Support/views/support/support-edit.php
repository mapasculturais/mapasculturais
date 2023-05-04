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
    mapas-card
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
    mc-alert
');

/* $breadcrumb = [
    ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => $opportunity->firstPhase->name, 'url' => $app->createUrl('opportunity', 'single', [$opportunity->firstPhase->id])],
]; */

/* $this->breadcrumb = $breadcrumb; */
?>

<div class="main-app support form">
    <mapas-breadcrumb></mapas-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <div class="support__content">
        <mapas-container>
            <main class="grid-12">
                <div class="col-12 title">
                    <?= i::__('Ficha de inscrição') ?>
                    <mc-alert type="helper">
                        <?= i::__('Você está realizando suporte dessa ficha de inscrição. Verifique os dados e corrija caso seja necessário.')?>
                    </mc-alert>
                </div>
                
                <div class="col-12">
                    
                    <div class="support-agent">
                        <div class="support-agent__image">
                            <img :src="entity.owner.files?.avatar?.transformations?.avatarMedium.url" />
                        </div>
                        <div class="support-agent__name">
                            {{entity.owner.name}}
                        </div>
                    </div>
                </div>

                <div class="col-12 support-info">
                    <p class="support-info__title"> <?= i::__('Informações da inscrição') ?> </p>
                    <div class="support-info__content">
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

                <div class="col-12">
                    <v1-embed-tool route="supporteditview" :id="entity.id"></v1-embed-tool>
                </div>
            </main>

            <aside>
                <div class="actions">
                    <button class="button button--primary button--md"> <?= i::__('Salvar alterações') ?> </button>
                    <button class="button button--primary-outline button--md"> <?= i::__('Sair') ?> </button>
                </div>
            </aside>
        </mapas-container>
    </div>
</div>