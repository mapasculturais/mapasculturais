<?php
use MapasCulturais\i;
$this->import('
    create-opportunity
    mc-icon 
    mc-title
    panel--evaluations-tabs
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon opportunity__background">
                    <mc-icon name="opportunity"></mc-icon>
                </div>

                <h2><?= i::__('Minhas validações') ?></h2>
            </div>
        </div>

        <p class="panel-page__header-subtitle">
            <?= i::__('Nesta seção você encontra as validações e os pareceres disponíveis para sua análise.') ?>
        </p>
    </header>

    <panel--evaluations-tabs :is-reporting-phase="true"></panel--evaluations-tabs>
</div>