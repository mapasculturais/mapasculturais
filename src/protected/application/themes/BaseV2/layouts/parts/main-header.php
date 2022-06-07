<?php
use MapasCulturais\i;
$this->import('popover');
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

    <!-- Menu mobile -->
    <ul class="main-header__menu-mobile">
        <li> 
            <a href="" class="main-header__menu-mobile--item home">
                <span class="icon"> <iconify icon="fluent:home-12-regular" /> </span>
                <p class="label"> Home </p>      
            </a>  
        </li>
        <li>
            <a href="" class="main-header__menu-mobile--item opportunities">
                <span class="icon"> <iconify icon="icons8:idea" /> </span>
                <p class="label"> Oportunidades </p>
            </a>
        </li>
        <li>
            <a href="" class="main-header__menu-mobile--item agents">
                <span class="icon"> <iconify icon="fa-solid:user-friends" /> </span>
                <p class="label"> Agentes </p>
            </a>
        </li>
        <li> 
            <a href="" class="main-header__menu-mobile--item events">
                <span class="icon"> <iconify icon="ant-design:calendar-twotone" /> </span>
                <p class="label"> Eventos </p>
            </a>  
        </li>
        <li> 
            <a href="" class="main-header__menu-mobile--item spaces">
                <span class="icon"> <iconify icon="clarity:building-line" /> </span>
                <p class="label"> Espaços </p>       
            </a> 
        </li>
        <li> 
            <a href="" class="main-header__menu-mobile--item projects">
                <span class="icon"> <iconify icon="ri:file-list-2-line" /> </span>
                <p class="label"> Projetos </p>      
            </a> 
        </li>
        
        <li> 
            <a href="" class="main-header__menu-mobile--item"> 
                <p class="label"> Painel de controle </p>
            </a> 
        </li>
        <li> 
            <a href="" class="main-header__menu-mobile--item"> 
                <p class="label"> Editais e Oportunidades </p>
            </a> 
        </li>
        <li> 
            <a href="" class="main-header__menu-mobile--item"> 
                <p class="label"> Meus eventos </p>
            </a> 
        </li>
        <li> 
            <a href="" class="main-header__menu-mobile--item"> 
                <p class="label"> Meus agentes </p>
            </a> 
        </li>
        <li> 
            <a href="" class="main-header__menu-mobile--item"> 
                <p class="label"> Meus espaços </p>
            </a> 
        </li>
    </ul>


    <!-- Options -->
    <div class="main-header__options">
        
        <div class="main-header__options--loggedIn active">

            <div class="notifications">
                <a class="desk" href=""> Notificações <iconify icon="eva:bell-outline" /> </a>
                <a class="mobile" href=""> <iconify icon="eva:bell-outline" /> </a>
            </div>
            
            <popover openside="down-left"> 
                <template #btn="{ onClick }">
                    <button :class="['openPopever', 'options']" @click="onClick"> Menu </button>
                </template>

                <template #content>
                    <ul class="menu-options">
                        <li> <a href=""> Painel de controle         </a> </li>
                        <li> <a href=""> Editais e Oportunidades    </a> </li>
                        <li> <a href=""> Meus eventos               </a> </li>
                        <li> <a href=""> Meus agentes               </a> </li>
                        <li> <a href=""> Meus espaços               </a> </li>
                    </ul>
                </template>
            </popover>
            
            <a class="btn-mobile">
                <iconify icon="icon-park-outline:hamburger-button" />
            </a>
            
        </div>

        <div class="main-header__options--loggedOff ">
            <a href="" class="logIn">
                <span><iconify icon="icon-park-outline:login" /></span>
                Entrar
            </a>
        </div>

    </div>

</header>