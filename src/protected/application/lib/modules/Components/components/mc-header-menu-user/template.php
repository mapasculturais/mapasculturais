<?php
use MapasCulturais\i;
$this->import('popover');
?>
<div class="mc-header-menu-user">

    <!-- Menu desktop -->
    <popover openside="down-left">
        <template #button="{ toggle }">
            <a class="mc-header-menu-user__user" @click="toggle()">
                <div class="mc-header-menu-user__user--name">
                    <?= i::_e('Minha conta') ?>
                </div>

                <div class="mc-header-menu-user__user--avatar">
                    <?php if ($app->user->profile->avatar): ?>
                        <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
                    <?php else: ?>
                        <mc-icon name="user"></mc-icon>
                    <?php endif; ?>
                </div>
            </a>
        </template>
        <template #default="popover">
            <ul class="mc-header-menu-user__desktop">
                <slot name="default"></slot>
            </ul>
        </template>
    </popover>

    <!-- Menu mobile -->
    <div class="mc-header-menu-user__mobile">
        <div class="mc-header-menu-user__mobile--btn">
            <a class="mc-header-menu-user__user" @click="toggle()">
                <div class="mc-header-menu-user__user--name">
                    <?= i::_e('Minha conta') ?>
                </div>

                <div class="mc-header-menu-user__user--avatar">
                    <?php if ($app->user->profile->avatar): ?>
                        <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
                    <?php else: ?>
                        <mc-icon name="user"></mc-icon>
                    <?php endif; ?>
                </div>
            </a>
        </div>    
        <ul v-if="open" class="mc-header-menu-user__mobile--list">
            <li class="close"> 
                <button class="close__btn" @click="toggleMobile()">
                    <mc-icon name="close"></mc-icon> 
                </button>
            </li>
            <slot name="default"></slot>
        </ul>
    </div>
    
</div>