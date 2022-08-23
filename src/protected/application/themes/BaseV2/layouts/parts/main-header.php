<?php
use MapasCulturais\App;
use MapasCulturais\i;
$this->import('popover messages main-menu theme-logo');
?>

<header class="main-header">

    <!-- Logo -->
    <div class="main-header__logo">
        <theme-logo title="mapa cultural" subtitle="do Pará" href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
    </div>     
    
    <!-- Menu -->
    <ul class="main-header__menu">
        <li> 
            <a href="<?= $app->createUrl('site', 'index') ?>" class="main-header__menu--item home">
                <span class="icon"> <mc-icon name="home"></mc-icon> </span>
                <p class="label"> <?php i::_e('Home') ?> </p>      
            </a>  
        </li>
        <li>
            <a href="<?= $app->createUrl('search', 'opportunities') ?>" class="main-header__menu--item opportunity">
                <span class="icon opportunity__bg-hover"> <mc-icon name="opportunity"></mc-icon> </span>
                <p class="label"> <?php i::_e('Oportunidades') ?> </p>
            </a>
        </li>
        <li>
            <a href="<?= $app->createUrl('search', 'agents') ?>" class="main-header__menu--item agent">
                <span class="icon"> <mc-icon name="agent-2"> </span>
                <p class="label"> <?php i::_e('Agentes') ?> </p>
            </a>
        </li>
        <li> 
            <a href="<?= $app->createUrl('search', 'events') ?>" class="main-header__menu--item event">
                <span class="icon"> <mc-icon name="event"> </span>
                <p class="label"> <?php i::_e('Eventos') ?> </p>
            </a>  
        </li>
        <li> 
            <a href="<?= $app->createUrl('search', 'spaces') ?>" class="main-header__menu--item space">
                <span class="icon"> <mc-icon name="space"> </span>
                <p class="label"> <?php i::_e('Espaços') ?> </p>       
            </a> 
        </li>
        <li> 
            <a href="<?= $app->createUrl('search', 'projects') ?>" class="main-header__menu--item project">
                <span class="icon"> <mc-icon name="project"> </span>
                <p class="label"> <?php i::_e('Projetos') ?> </p>      
            </a> 
        </li>
    </ul>


    <!-- Options -->
    <div class="main-header__options">
        <?php if ($app->user->is('guest')): ?>
            
            <div class="main-header__options--loggedOff">
                <a href="<?= $app->createUrl('auth') ?>" class="logIn">
                    <span><mc-icon name="project"></mc-icon></span>
                    <?php i::_e('Entrar') ?>
                </a>
            </div>
            
        <?php else: ?>

            <div class="main-header__options--loggedIn active">
                <div class="notifications">
                    <a class="desk" href=""> <?php i::_e('Notificações') ?> <mc-icon name="notification"></mc-icon> </a>
                    <a class="mobile" href=""> <mc-icon name="notification"></mc-icon> </a>
                </div>

                <main-menu>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'index') ?>"> 
                            <p class="label"> <?php i::_e('Painel de controle') ?> </p>
                        </a> 
                    </li>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'opportunities') ?>"> 
                            <p class="label"> <?php i::_e('Editais e Oportunidades') ?> </p>
                        </a> 
                    </li>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'events') ?>"> 
                            <p class="label"> <?php i::_e('Meus eventos') ?> </p>
                        </a> 
                    </li>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'agents') ?>"> 
                            <p class="label"> <?php i::_e('Meus agentes') ?> </p>
                        </a> 
                    </li>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'spaces') ?>"> 
                            <p class="label"> <?php i::_e('Meus espaços') ?> </p>
                        </a> 
                    </li>
                    <li>
                        <a href="<?= $app->createUrl('auth', 'logout') ?>">
                            <p class="label"> <?php i::_e('Sair') ?> </p>
                        </a>
                    </li>
                </main-menu>
            </div>

        <?php endif; ?>
    </div>

</header>
<messages></messages>