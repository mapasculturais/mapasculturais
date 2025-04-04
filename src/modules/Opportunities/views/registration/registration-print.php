<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-summary-agent
    mc-summary-agent-info
    mc-summary-project
    mc-summary-spaces
    registration-info
    v1-embed-tool
    opportunity-phases-timeline
    mc-card
');

$entity = $entity->firstPhase;

$this->enqueueScript('app-v2', 'registration-print', 'js/registration-print.js');
?>

<main class="print-registration grid-12">
    <mc-summary-agent :entity="entity" classes="col-12 print__side-registration-padding"></mc-summary-agent>
    <registration-info :registration="entity" classes="col-12 print__side-registration-padding"></registration-info>
    <div class="col-12 bold" v-if="entity.sentTimestamp" class="sentDate"> 
        <?= i::__('Inscrição realizada em') ?> {{entity.sentTimestamp.date('2-digit year')}} <?= i::__('às') ?> {{entity.sentTimestamp.time('long')}} 
    </div>
    <opportunity-phases-timeline class="col-12" center big></opportunity-phases-timeline>
    <mc-summary-agent-info :entity="entity" classes="col-12"></mc-summary-agent-info>
    <h3 class="col-12 print__side-registration-padding"><?= i::__('Dados informados no formulário') ?></h3>
    <mc-summary-spaces style="justify-content: center;" :entity="entity" classes="col-12"></mc-summary-spaces>
    <mc-summary-project :entity="entity" classes="col-12"></mc-summary-project>

    <section class="col-12 section">
        <?php $this->applyTemplateHook('section', 'begin') ?>
        <div class="section__content">
            <div class="card owner">

                <?php while($entity): $opportunity = $entity->opportunity;?>
                    <?php if($opportunity->isDataCollection):?>
                        <?php if($opportunity->isFirstPhase):?>
                            <h2><?= i::__('Inscrição') ?></h2>
                        <?php else: ?>
                            <h2><?= $opportunity->name ?></h2>
                        <?php endif; ?>

                        <v1-embed-tool route="registrationview" :id="<?=$entity->id?>"></v1-embed-tool>
                    <?php endif ?>
                    <?php $entity = $entity->nextPhase; ?>
                <?php endwhile ?>
                
            </div>
        </div>
        <?php $this->applyTemplateHook('section', 'end') ?>
    </section>
</main>