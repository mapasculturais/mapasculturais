<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * @var \MapasCulturais\Entities\Registration $entity
 */

use MapasCulturais\i;

$this->layout = 'registrations';

$this->import('
    mc-avatar
    mc-alert
    mc-breadcrumb
    mc-container
    opportunity-header
    registration-info 
    support-actions
    v1-embed-tool
');

/* $breadcrumb = [
    ['label' => i::__('Oportunidades'), 'url' => $app->createUrl('panel', 'opportunities')],
    ['label' => $opportunity->firstPhase->name, 'url' => $app->createUrl('opportunity', 'single', [$opportunity->firstPhase->id])],
]; */

/* $this->breadcrumb = $breadcrumb; */
?>

<div class="main-app support form">
    <mc-breadcrumb></mc-breadcrumb>
    <opportunity-header :opportunity="entity.opportunity"></opportunity-header>

    <div class="support__content">
        <mc-container>
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
                            <mc-avatar :entity="entity.owner" size="small"></mc-avatar>
                        </div>
                        <div class="support-agent__name">
                            {{entity.owner.name}}
                        </div>
                    </div>
                </div>

                <registration-info :registration="entity" classes="col-12"></registration-info>

                <div class="col-12">
                    <v1-embed-tool iframe-id="support-form" route="supporteditview" :id="entity.id"></v1-embed-tool>
                </div>
            </main>

            <aside>
                <support-actions :registration="entity"></support-actions>
                <!-- <div class="actions">
                    <button class="button button--primary button--md"> <?= i::__('Salvar alterações') ?> </button>
                    <button class="button button--primary-outline button--md"> <?= i::__('Sair') ?> </button>
                </div> -->
            </aside>
        </mc-container>
    </div>
</div>