<?php
$this->import('popover');
?>

<header class="main-header">

    <div class="main-header__logo">
        <h1>Mapa Cultural</h1>
    </div>     

    <ul class="main-header__menu">
        <a href="" class="main-header__menu--item home">
            <li> 
                <span class="icon"> <iconify icon="fluent:home-12-regular" /> </span>
                <p class="label"> Home </p>      
            </li>
        </a>  
        <a href="" class="main-header__menu--item opportunities">
            <li>
                <span class="icon"> <iconify icon="icons8:idea" /> </span>
                <p class="label"> Oportunidades </p>
            </li>
        </a>
        <a href="" class="main-header__menu--item agents">
            <li>
                <span class="icon"> <iconify icon="fa-solid:user-friends" /> </span>
                <p class="label"> Agentes </p>
            </li>
        </a>
        <a href="" class="main-header__menu--item events">
            <li> 
                <span class="icon"> <iconify icon="ant-design:calendar-twotone" /> </span>
                <p class="label"> Eventos </p>
            </li>
        </a>  
        <a href="" class="main-header__menu--item spaces">
            <li> 
                <span class="icon"> <iconify icon="clarity:building-line" /> </span>
                <p class="label"> Espaços </p>       
            </li>
        </a> 
        <a href="" class="main-header__menu--item projects">
            <li> 
                <span class="icon"> <iconify icon="ri:file-list-2-line" /> </span>
                <p class="label"> Projetos </p>      
            </li>
        </a> 
    </ul>

    <div class="main-header__options">
        
        <div class="main-header__options--loggedIn active">
            <div class="notifications">
                <a class="desk" href=""> Notificações <iconify icon="eva:bell-outline" /> </a>
                <a class="mobile" href=""> <iconify icon="eva:bell-outline" /> </a>
            </div>
            <popover openside="down-left" label="menu" classbnt="options"> 
                <ul>
                    <li> <a href=""> Painel de controle         </a> </li>
                    <li> <a href=""> Editais e Oportunidades    </a> </li>
                    <li> <a href=""> Meus eventos               </a> </li>
                    <li> <a href=""> Meus agentes               </a> </li>
                    <li> <a href=""> Meus espaços               </a> </li>
                </ul>
            </popover>
        </div>

        <div class="main-header__options--loggedOff ">
            <a href="" class="logIn">
                <span><iconify icon="icon-park-outline:login" /></span>
                Entrar
            </a>
        </div>

    </div>

</header>