<?php
use MapasCulturais\i;
$this->import('
    panel--nav
    popover
    theme-logo
');
?>
<div class="mc-header-menu-user">

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
            <panel--nav classes="mc-header-menu-user__itens">
                <template #begin>
                    <ul>
                        <li><mc-link :entity='profile' icon><?= i::__('Meu Perfil') ?></mc-link></li>
                        <li><mc-link route='auth/logout' icon="logout"><?= i::__('Sair') ?></mc-link></li>
                    </ul>
                    <h3><?= i::__('Menu do Painel de controle') ?></h3>
                </template>
            </panel--nav>
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

            <panel--nav :class="mc-header-menu-user__itens"></panel--nav>
        </div>
    </div>
    
</div>