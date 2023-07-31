<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-popover
    panel--nav
    theme-logo
    user-profile-avatar
');
?>
<?php $this->applyTemplateHook('header-menu-user', 'before') ?>
<div class="mc-header-menu-user">
    <?php $this->applyTemplateHook('header-menu-user', 'begin') ?>
    <!-- Menu desktop -->
    <?php $this->applyTemplateHook('header-menu-user--desktop', 'before'); ?>
    <mc-popover openside="down-left">
        <template #button="{ toggle }">
        <div class="mc-header-menu-user__desktop">
            <a class="user" @click="toggle()">
                <div class="user__name">
                    <?= i::_e('Minha conta') ?>
                </div>
                <div class="user__avatar">
                    <user-profile-avatar></user-profile-avatar>
                </div>
            </a>
        </div>
        </template>
        <template #default="popover">
            <?php $this->applyTemplateHook('header-menu-user--desktop', 'before') ?>
            <panel--nav classes="user-menu">
                <template #begin>
                    <?php $this->applyTemplateHook('header-menu-user--desktop', 'begin') ?>
                    <ul>
                        <?php $this->applyTemplateHook('header-menu-user--itens', 'begin') ?>


                        <?php $this->applyTemplateHook('header-menu-user--itens', 'end') ?>
                    </ul>
                </template>

                <template #end>
                    <div class="user-menu__line"></div>
                    <li><mc-link :entity='profile' icon><?= i::__('Meu Perfil') ?></mc-link></li>
                    <li><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li>
                    <?php $this->applyTemplateHook('header-menu-user--desktop', 'end') ?>
                </template>
            </panel--nav>
        </template>
    </mc-popover>
    <?php $this->applyTemplateHook('header-menu-user--desktop', 'after'); ?>

    <!-- Menu mobile -->
    <?php $this->applyTemplateHook('header-menu-user--mobile', 'before'); ?>
    <div class="mc-header-menu-user__mobile">
        <?php $this->applyTemplateHook('header-menu-user--mobile', 'begin'); ?>
        <div class="mc-header-menu-user__mobile--button">
            <a href="#main-app" class="user" @click="toggleMobile()">
                <div class="user__name">
                    <?= i::_e('Minha conta') ?>
                </div>
                <div class="user__avatar">
                    <user-profile-avatar></user-profile-avatar>
                </div>
            </a>
        </div>
        <div v-if="open" class="mc-header-menu-user__mobile--list">
            <div class="close">
                <theme-logo href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
                <a class="close__btn" href="#main-app" @click="toggleMobile()">
                    <mc-icon name="close"></mc-icon>
                </a>
            </div>
            <?php $this->applyTemplateHook('header-menu-user--mobile', 'before') ?>
            <panel--nav>
                <template #begin>

                    <?php $this->applyTemplateHook('header-menu-user--mobile', 'begin') ?>
                </template>

                <template #end>
                    <mc-link :entity='profile' icon><label><?= i::__('Meu Perfil') ?></label></mc-link>
                    <mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link>
                    <?php $this->applyTemplateHook('header-menu-user--mobile', 'end') ?>
                </template>
            </panel--nav>
            <?php $this->applyTemplateHook('header-menu-user--mobile', 'after') ?>
        </div>
        <?php $this->applyTemplateHook('header-menu-user--mobile', 'end'); ?>
    </div>
    <?php $this->applyTemplateHook('header-menu-user', 'end') ?>
</div>
<?php $this->applyTemplateHook('header-menu-user', 'after') ?>