<?php
use MapasCulturais\i;
$this->import('popover theme-logo');
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
            <ul class="mc-header-menu-user__itens">
                <slot name="default"></slot>
            </ul>
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
                <theme-logo title="mapa cultural" subtitle="do Pará" href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>

                <button class="close__btn" @click="toggleMobile()">
                    <mc-icon name="close"></mc-icon> 
                </button>
            </div>

            <ul class="mc-header-menu-user__itens">
                <slot name="default"></slot>
            </ul>
        </div>
    </div>
    
</div>