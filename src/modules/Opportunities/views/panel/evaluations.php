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

                <mc-title tag="h1" short-length="0" :long-length="100" size="big"><?= i::__('Minhas avaliações') ?></mc-title>
            </div>
        </div>

        <p class="panel-page__header-subtitle">
            <?= i::__('Nesta seção você encontra as avaliações e os pareceres disponíveis para sua análise.') ?>
        </p>
    </header>

    <panel--evaluations-tabs></panel--evaluations-tabs>
</div>