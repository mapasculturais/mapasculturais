<?php

use MapasCulturais\i;

$this->import('
    mc-icon
    panel--entity-tabs 
');
?>

<div class="panel-page">
    <header class="panel-page__header">
        <div class="panel-page__header-title">
            <div class="title">
                <div class="title__icon primary__background"> <mc-icon name="subsite"></mc-icon> </div>
                <h1 class="title__title"> <?= i::_e('Meus subsites') ?> </h1>
            </div>
        </div>
        <p class="panel-page__header-subtitle">
            <?= i::_e('Nesta seção você visualiza e gerencia os subsites do sistema') ?>
        </p>
        <div class="panel-page__header-actions">
            <a href="<?= $app->createUrl('subsite', 'create') ?>" class="button button--primary button--icon">
                <mc-icon name="add"></mc-icon>
                <span><?= i::__('Criar subsite') ?></span>
            </a>
        </div>
    </header>

    <panel--entity-tabs type="subsite" user="" tabs="publish,draft,trash" select="id,status,name,createTimestamp,owner,url"></panel--entity-tabs>
</div>
