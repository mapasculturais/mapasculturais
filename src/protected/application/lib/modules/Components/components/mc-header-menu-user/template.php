<?php
/**
 * @var MapasCulturais\Themes\BaseV2 $this
 * @var MapasCulturais\App $app
 */
use MapasCulturais\i;
$this->import('
    panel--nav
    popover
    theme-logo
');
?>
<?php $this->applyTemplateHook('header-menu-user', 'before') ?>
<div class="mc-header-menu-user">
    <?php $this->applyTemplateHook('header-menu-user', 'begin') ?>
    <!-- Menu desktop -->
    <popover openside="down-left" class="mc-header-menu-user__desktop">

        <template #button="{ toggle }">            
            <a class="mc-header-menu-user__user" @click="toggle()">
                <div class="mc-header-menu-user__user--name">
                    <?= i::_e('Minha conta') ?>
                </div>

                <div class="mc-header-menu-user__user--avatar">
                    <?php if (!$app->user->is('guest')): ?>
                        <?php if ($app->user->profile->avatar): ?>
                            <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
                        <?php else: ?>
                            <mc-icon name="user"></mc-icon>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </a>
        </template>

        <template #default="popover">
            <?php $this->applyTemplateHook('header-menu-user--desktop', 'before') ?>
            <panel--nav classes="user-menu">
                <template #begin>
                    <?php $this->applyTemplateHook('header-menu-user--desktop', 'begin') ?>
                    <ul>
                        <?php $this->applyTemplateHook('header-menu-user--itens', 'begin') ?>
                        <li><mc-link :entity='profile' icon><?= i::__('Meu Perfil') ?></mc-link></li>
                        <li><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li>
                        <?php $this->applyTemplateHook('header-menu-user--itens', 'end') ?>
                    </ul>
                    <h3><?= i::__('Menu do Painel de controle') ?></h3>
                </template>

                <template #end>
                    <?php $this->applyTemplateHook('header-menu-user--desktop', 'end') ?>
                </template>
            </panel--nav>
            <?php $this->applyTemplateHook('header-menu-user--desktop', 'after') ?>
        </template>

    </popover>

    <!-- Menu mobile -->
    <div class="mc-header-menu-user__mobile">
        
        <div class="mc-header-menu-user__mobile--button">            
            <a href="#main-app" class="mc-header-menu-user__user" @click="toggleMobile()">
                <div class="mc-header-menu-user__user--name">
                    <?= i::_e('Minha conta') ?>
                </div>

                <div class="mc-header-menu-user__user--avatar">
                    <?php if (!$app->user->is('guest')): ?>
                        <?php if ($app->user->profile->avatar): ?>
                            <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
                        <?php else: ?>
                            <mc-icon name="user"></mc-icon>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </a>            
        </div>    

        <div v-if="open" class="mc-header-menu-user__mobile--list">
            <div class="close"> 
                <theme-logo href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>

                <a class="close__btn"  href="#main-app" @click="toggleMobile()">
                    <mc-icon name="close"></mc-icon> 
                </a>
            </div>
            <?php $this->applyTemplateHook('header-menu-user--mobile', 'before') ?>
            <panel--nav>
                <template #begin>
                    <?php $this->applyTemplateHook('header-menu-user--mobile', 'begin') ?>
                    <mc-link :entity='profile' icon><?= i::__('Meu Perfil') ?></mc-link>
                    <mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link>
                </template>
                <template #end>
                    <?php $this->applyTemplateHook('header-menu-user--mobile', 'end') ?>
                </template>
            </panel--nav>
            <?php $this->applyTemplateHook('header-menu-user--mobile', 'after') ?>
        </div>
    </div>
    <?php $this->applyTemplateHook('header-menu-user', 'end') ?>
</div>
<?php $this->applyTemplateHook('header-menu-user', 'after') ?>