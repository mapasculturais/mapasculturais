<?php
use MapasCulturais\i;
$this->import('
    create-opportunity
    mc-icon 
    panel--entity-card
    panel--entity-tabs
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon opportunity__background">
                    <mc-icon name="opportunity"></mc-icon>
                </div>
                <div class="title__title"> <?= i::_e('Minhas oportunidades') ?> </div>
            </div>
            <div class="help">
                <a class="panel__help-link" href="#"><?= i::__('Ajuda?') ?></a>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você pode adicionar e gerenciar suas oportunidades') ?>
        </p>
        <div class="panel-page__header-actions">
            <create-opportunity  :editable="true" #default="{modal}"  >
                <button @click="modal.open()" class="button button--primary button--icon">
                    <mc-icon name="add"></mc-icon>
                    <span><?= i::__('Criar Oportunidade') ?></span>
                </button>
            </create-project>
        </div>
    </header>

    <panel--entity-tabs type="opportunity"></panel--entity-tabs>
</div>