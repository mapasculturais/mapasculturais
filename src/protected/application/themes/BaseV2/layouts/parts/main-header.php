<?php
use MapasCulturais\App;
use MapasCulturais\i;
$this->import('popover messages theme-logo mc-icon
    mc-header-menu-user
    mc-header-menu

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
            <mc-header-menu-user>
                <template #default>
                    <li>
                        <a href="<?= $app->createUrl('auth', 'logout') ?>">
                            <mc-icon name="agent-1"></mc-icon>
                            <label> <?php i::_e('Meu Perfil') ?> </label>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $app->createUrl('auth', 'logout') ?>">
                            <mc-icon name="logout"></mc-icon>
                            <label> <?php i::_e('Sair da sua conta') ?> </label>
                        </a>
                    </li>

                    <li class="label">
                        <?= i::_e('Menu do painel de controle')?>
                    </li>

                    <li> 
                        <a href="<?= $app->createUrl('panel', 'index') ?>"> 
                            <mc-icon name="dashboard"></mc-icon>
                            <label> <?php i::_e('Painel de controle') ?> </label>
                        </a> 
                    </li>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'agents') ?>"> 
                            <mc-icon name="agent"></mc-icon>
                            <label> <?php i::_e('Meus agentes') ?> </label>
                        </a> 
                    </li>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'events') ?>"> 
                            <mc-icon name="event"></mc-icon>
                            <label> <?php i::_e('Meus eventos') ?> </label>
                        </a> 
                    </li>
                    <li> 
                        <a href="<?= $app->createUrl('panel', 'spaces') ?>"> 
                            <mc-icon name="space"></mc-icon>
                            <label> <?php i::_e('Meus espaços') ?> </label>
                        </a> 
                    </li>

                    <li class="label">
                        <?= i::_e('Editais e oportunidades')?>
                    </li>

                    <li>
                        <a href="<?= $app->createUrl('panel', 'registrations') ?>"> 
                            <mc-icon name="opportunity"></mc-icon>
                            <label> <?php i::_e('Minhas inscrições') ?> </label>
                        </a>
                    </li>
                    <li>
                        <a href="<?= $app->createUrl('panel', 'opportunities') ?>"> 
                            <mc-icon name="opportunity"></mc-icon>
                            <label> <?php i::_e('Minhas oportunidades') ?> </label>
                        </a>
                    </li>
                    <li>
                        <a href=""> 
                            <mc-icon name="opportunity"></mc-icon>
                            <label> <?php i::_e('Prestação de contas') ?> </label>
                        </a>
                    </li>
                    <li>
                        <a href=""> 
                            <mc-icon name="opportunity"></mc-icon>
                            <label> <?php i::_e('Minhas avaliações') ?> </label>
                        </a>
                    </li>
                </template>    
            </mc-header-menu-user>
        <?php endif; ?>

    </div>

</header>

<messages></messages>