<?php
use MapasCulturais\i;
$this->import('
    mc-icon 
    panel--entity-card
    panel--entity-tabs 
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon app__background">
                    <mc-icon name="app"></mc-icon>
                </div>
                <div class="title__title"> <?= i::_e('Meus aplicativos') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar seus aplicativos') ?>
        </p>
        <div class="panel-page__header-actions">
            
        </div>
    </header>

    <panel--entity-tabs tabs="publish,trash" type="app"></panel--entity-tabs>
</div>