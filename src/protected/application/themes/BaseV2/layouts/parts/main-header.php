<?php
use MapasCulturais\App;
use MapasCulturais\i;
$this->import('
    messages 
    mc-header-menu
    mc-header-menu-user
    mc-icon
    theme-logo 
    view-notification
');

?>

<header class="main-header">

    <div class="main-header__content">

        <mc-header-menu>
            <!-- Logo -->
            <template #logo>
                <theme-logo title="mapa cultural" subtitle="do Pará" href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
            </template>
            <!-- Menu principal -->
            <template #default>
                <li> 
                    <a href="<?= $app->createUrl('site', 'index') ?>" class="mc-header-menu--item home">
                        <span class="icon"> <mc-icon name="home"></mc-icon> </span>
                        <p class="label"> <?php i::_e('Home') ?> </p>      
                    </a>  
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'opportunities') ?>" class="mc-header-menu--item opportunity">
                        <span class="icon opportunity__bg-hover"> <mc-icon name="opportunity"></mc-icon> </span>
                        <p class="label"> <?php i::_e('Oportunidades') ?> </p>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'agents') ?>" class="mc-header-menu--item agent">
                        <span class="icon"> <mc-icon name="agent-2"> </span>
                        <p class="label"> <?php i::_e('Agentes') ?> </p>
                    </a>
                </li>
                <li> 
                    <a href="<?= $app->createUrl('search', 'events') ?>" class="mc-header-menu--item event">
                        <span class="icon"> <mc-icon name="event"> </span>
                        <p class="label"> <?php i::_e('Eventos') ?> </p>
                    </a>  
                </li>
                <li> 
                    <a href="<?= $app->createUrl('search', 'spaces') ?>" class="mc-header-menu--item space">
                        <span class="icon"> <mc-icon name="space"> </span>
                        <p class="label"> <?php i::_e('Espaços') ?> </p>       
                    </a> 
                </li>
                <li> 
                    <a href="<?= $app->createUrl('search', 'projects') ?>" class="mc-header-menu--item project">
                        <span class="icon"> <mc-icon name="project"> </span>
                        <p class="label"> <?php i::_e('Projetos') ?> </p>      
                    </a> 
                </li>
                <?php if (!$app->user->is('guest')): ?>
                    <li>
                        <view-notification :editable="true" #default="{modal}"  >
                            <button @click="modal.open()" class="button button--primary button--icon">
                                <mc-icon name="notification"></mc-icon>
                            </button>
                        </view-notification>
                    </li>
                <?php endif; ?>
            </template>
        </mc-header-menu>
        
        <!--  -->
        
        <?php if ($app->user->is('guest')): ?>   
            <!-- Botão login -->             
            <a href="<?= $app->createUrl('auth') ?>" class="logIn">
                <?php i::_e('Entrar') ?>
            </a>        
        <?php else: ?>
            <!-- Menu do usuário -->
            <mc-header-menu-user></mc-header-menu-user>
        <?php endif; ?>

    </div>

</header>

<messages></messages>