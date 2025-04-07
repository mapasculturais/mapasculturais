<?php
use MapasCulturais\i;

$this->import('
    mc-icon
    panel--registration-tabs
');
?>

<div class="panel-page registrations">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon opportunity__background">
                    <mc-icon name="opportunity"></mc-icon>
                </div>
                <h1 class="title__title"> <?= $this->text('my-registrations', i::__('Minhas inscrições')) ?></h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= $this->text('my-registrations-records', i::__('Nesta seção você pode adicionar e gerenciar suas inscrições')) ?>
        </p>
        <div class="panel-page__header-actions">
            
        </div>
    </header>

    <panel--registration-tabs></panel--registration-tabs>
</div>