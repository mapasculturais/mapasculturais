<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-tab
    mc-tabs
    panel--entities-summary 
    panel--entity-tabs 
    panel--last-edited
    panel--last-registrations
    panel--open-opportunities
    panel--pending-evaluations
    user-profile-avatar
'); 
?>
<div class="panel-home">
    <?php $this->applyTemplateHook('header', 'before') ?>
    <header class="panel-home__header">
        <?php $this->applyTemplateHook('header', 'begin') ?>
        <?php $this->applyTemplateHook('header-title', 'before') ?>
        <div class="panel-home__header--title">
            <?php $this->applyTemplateHook('header-title', 'begin') ?>
            <label class="title"> <?= i::_e('Painel de controle') ?> </label>
            <?php $this->applyTemplateHook('header-title', 'end') ?>
        </div>
        <?php $this->applyTemplateHook('header-title', 'after') ?>

        <?php $this->applyTemplateHook('header-user', 'before') ?>
        <div class="panel-home__header--user">
            <?php $this->applyTemplateHook('header-user', 'begin') ?>
            <div class="panel-home__header--user-profile">
                <div class="avatar">
                    <user-profile-avatar></user-profile-avatar>
                </div>
                <div class="name">
                    <?= i::_e('Olá, ') ?> <?= $app->user->profile->name ?>
                </div>
            </div>
            <div class="panel-home__header--user-button">
                <?php $this->applyTemplateHook('header-user-button', 'before') ?>
                <a href="<?= $app->user->profile->singleUrl ?>" class="button button--primary button--icon"> <mc-icon name="agent-1"></mc-icon> <?= i::_e('Acessar meu perfil') ?> </a>
                <?php $this->applyTemplateHook('header-user-button', 'after') ?>
            </div>
            <?php $this->applyTemplateHook('header-user', 'end') ?>
        </div>
        <?php $this->applyTemplateHook('header-user', 'after') ?>
        <?php $this->applyTemplateHook('header', 'end') ?>
    </header>
    <?php $this->applyTemplateHook('header', 'after') ?>
    
    <?php $this->applyTemplateHook('tabs', 'before') ?>
    <mc-tabs class="panel-home__tabs">
        <?php $this->applyTemplateHook('tabs', 'begin') ?>
        <mc-tab label="<?php i::esc_attr_e('Principal') ?>" slug="main">
            <div class="panel-home__tabs--main">

                <panel--entities-summary></panel--entities-summary>

                <panel--pending-evaluations></panel--pending-evaluations>

                <panel--open-opportunities></panel--open-opportunities>

                <panel--last-registrations></panel--last-registrations>

                <panel--last-edited></panel--last-edited>

            </div>
        </mc-tab>
        <?php $this->applyTemplateHook('tabs', 'end') ?>
    </mc-tabs>
    <?php $this->applyTemplateHook('tabs', 'after') ?>
</div>