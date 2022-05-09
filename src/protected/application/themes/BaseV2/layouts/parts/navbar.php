<?php
use MapasCulturais\i;
?>
<header class="main-navbar">
    <nav>
        <ul>
            <li>
                <a class="main-navbar__link home" href="#">
                    <iconify icon="mdi:home-outline"></iconify>
                    <span><?= i::__('Início') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link opportunities" href="#">
                    <iconify icon="mdi:lightbulb-on-outline"></iconify>
                    <span><?= i::__('Editais') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link agents active" href="#">
                    <iconify icon="mdi:account-multiple-outline"></iconify>
                    <span><?= i::__('Agentes') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link events" href="#">
                    <iconify icon="mdi:calendar-month"></iconify>
                    <span><?= i::__('Eventos') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link spaces" href="#">
                    <iconify icon="mdi:domain"></iconify>
                    <span><?= i::__('Espaços') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link projects" href="#">
                    <iconify icon="mdi:clipboard-list-outline"></iconify>
                    <span><?= i::__('Projetos') ?></span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="main-navbar__notifications">
        <span><?= i::__('Notificações') ?></span>
        <div class="main-navbar__notifications-count">
            <iconify icon="mdi:bell-outline"></iconify>
            <span>1</span>
        </div>
    </div>
    <div class="main-navbar__dropdown">
        <a href="#"><?= i::__('Painel de controles') ?></a>
    </div>
</header>
