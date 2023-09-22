<?php
use MapasCulturais\i;

$this->import('
    mc-summary-agent
    mc-summary-agent-info
    mc-summary-project
    mc-summary-spaces
    registration-info
    v1-embed-tool
    opportunity-phases-timeline
');
?>

<main class="print-registration grid-12">
    <mc-summary-agent :entity="entity" classes="col-12 print__side-registration-padding"></mc-summary-agent>
    <registration-info :registration="entity" classes="col-12 print__side-registration-padding"></registration-info>
    <opportunity-phases-timeline class="col-12" center big></opportunity-phases-timeline>
    <mc-summary-agent-info :entity="entity" classes="col-12"></mc-summary-agent-info>
    <h3 class="col-12 print__side-registration-padding"><?= i::__('Dados informados no formulário') ?></h3>
    <mc-summary-spaces style="justify-content: center;" :entity="entity" classes="col-12"></mc-summary-spaces>
    <mc-summary-project :entity="entity" classes="col-12"></mc-summary-project>

    <section class="col-12 section">
        <div class="section__content">
            <div class="card owner">
                <v1-embed-tool route="registrationview" :id="entity.id"></v1-embed-tool>
            </div>
        </div>
    </section>
</main>