<?php
use MapasCulturais\App;
use MapasCulturais\i;
$this->import('popover messages main-menu');
?>

<header class="main-header">

    <!-- Logo -->
    <div class="main-header__logo">
        <h1>Mapa Cultural</h1>
    </div>     

    
    <!-- Menu -->
    <ul class="main-header__menu">
        <li> 
            <a href="" class="main-header__menu--item home">
                <span class="icon"> <iconify icon="fluent:home-12-regular" /> </span>
                <p class="label"> <?php i::_e('Home') ?> </p>      
            </a>  
        </li>
        <li>
            <a href="" class="main-header__menu--item oportunity">
                <span class="icon opportunity__bg-hover"> <iconify icon="icons8:idea" /> </span>
                <p class="label"> <?php i::_e('Oportunidades') ?> </p>
            </a>
        </li>
        <li>
            <a href="" class="main-header__menu--item agent">
                <span class="icon"> <iconify icon="fa-solid:user-friends" /> </span>
                <p class="label"> <?php i::_e('Agentes') ?> </p>
            </a>
        </li>
        <li> 
            <a href="" class="main-header__menu--item event">
                <span class="icon"> <iconify icon="ant-design:calendar-twotone" /> </span>
                <p class="label"> <?php i::_e('Eventos') ?> </p>
            </a>  
        </li>
        <li> 
            <a href="" class="main-header__menu--item space">
                <span class="icon"> <iconify icon="clarity:building-line" /> </span>
                <p class="label"> <?php i::_e('Espaços') ?> </p>       
            </a> 
        </li>
        <li> 
            <a href="" class="main-header__menu--item project">
                <span class="icon"> <iconify icon="ri:file-list-2-line" /> </span>
                <p class="label"> <?php i::_e('Projetos') ?> </p>      
            </a> 
        </li>
    </ul>


    <!-- Options -->
    <div class="main-header__options">
        <?php if ($app->user->is('guest')): ?>
            
            <div class="main-header__options--loggedOff">
                <a href="" class="logIn">
                    <span><iconify icon="icon-park-outline:login" /></span>
                    Entrar
                </a>
            </div>
            
        <?php else: ?>

            <div class="main-header__options--loggedIn active">
                <div class="notifications">
                    <a class="desk" href=""> Notificações <iconify icon="eva:bell-outline" /> </a>
                    <a class="mobile" href=""> <iconify icon="eva:bell-outline" /> </a>
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