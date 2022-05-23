<li class="user">
    <a href="javascript:void(0);" class="js-submenu-toggle" data-submenu-target="$(this).parent().find('.submenu')" rel='noopener noreferrer'>
        <div class="avatar">
            <?php if ($app->user->profile->avatar): ?>
                <img src="<?php echo $app->user->profile->avatar->transform('avatarSmall')->url; ?>" />
            <?php else: ?>
                <img src="<?php $this->asset('img/avatar--agent.png'); ?>" />
            <?php endif; ?>
        </div>
    </a>
    <ul class="submenu hidden">
        <li>
            <a href="<?php echo $app->user->profile->editUrl; ?>"><?php \MapasCulturais\i::_e("Meu perfil");?></a>
        </li>
        <hr>
        <li>
            <a href="<?php echo $app->createUrl('auth', 'logout'); ?>"><?php \MapasCulturais\i::_e("Sair");?></a>
        </li>
    </ul>
</li>

