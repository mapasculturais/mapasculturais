<?php
use MapasCulturais\i;
?>
<header class="main-navbar">
    <nav>
        <ul>
            <li>
                <a class="main-navbar__link home" href="#">
                    <mc-icon name="home"></mc-icon>
                    <span><?= i::__('Início') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link opportunities" href="#">
                    <mc-icon name="opportunity"></mc-icon>
                    <span><?= i::__('Editais') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link agents active" href="#">
                    <mc-icon name="agent"></mc-icon>
                    <span><?= i::__('Agentes') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link events" href="#">
                    <mc-icon name="event"></mc-icon>
                    <span><?= i::__('Eventos') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link spaces" href="#">
                    <mc-icon name="space"></mc-icon>
                    <span><?= i::__('Espaços') ?></span>
                </a>
            </li>
            <li>
                <a class="main-navbar__link projects" href="#">
                    <mc-icon name="project"></mc-icon>
                    <span><?= i::__('Projetos') ?></span>
                </a>
            </li>
        </ul>
    </nav>
    <div class="main-navbar__notifications">
        <span><?= i::__('Notificações') ?></span>
        <div class="main-navbar__notifications-count">
            <mc-icon name="notification"></mc-icon>
            <span>1</span>
        </div>
    </div>
    <div class="main-navbar__dropdown">
        <a href="#"><?= i::__('Painel de controles') ?></a>
    </div>
</header>
