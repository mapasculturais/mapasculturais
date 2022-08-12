<?php
use MapasCulturais\i;
?>
<div class="panel-sidebar">
    <h2>
        <button class="button panel-sidebar__toggle" @click="open = !open">
            <mc-icon v-show="open" name="up"></mc-icon>
            <mc-icon v-show="!open" name="down"></mc-icon>
            <span><?=i::__('Menu do painel')?></span>
        </button>
    </h2>
    <nav :class="{ '--open': open }">
        <ul>
            <li>
                <a href="<?= $this->controller->createUrl('index') ?>">
                    <mc-icon name="dashboard"></mc-icon>
                    <span><?=i::__('Visão geral')?></span>
                </a>
            </li>
            <li>
                <a href="<?= $this->controller->createUrl('agents') ?>">
                    <mc-icon name="agent"></mc-icon>
                    <span><?=i::__('Meus agentes')?></span>
                </a>
            </li>
            <li>
                <a href="<?= $this->controller->createUrl('spaces') ?>">
                    <mc-icon name="space"></mc-icon>
                    <span><?=i::__('Meus espaços')?></span>
                </a>
            </li>
            <li>
                <a href="<?= $this->controller->createUrl('events') ?>">
                    <mc-icon name="event"></mc-icon>
                    <span><?=i::__('Meus eventos')?></span>
                </a>
            </li>
            <li>
                <a href="<?= $this->controller->createUrl('projects') ?>">
                    <mc-icon name="project"></mc-icon>
                    <span><?=i::__('Meus projetos')?></span>
                </a>
            </li>
        </ul>
        <h3><?=i::__('Editais e oportunidades')?></h3>
        <ul>
            <li>
                <a href="#">
                    <mc-icon name="opportunity"></mc-icon>
                    <span><?=i::__('Minhas inscrições')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <mc-icon name="opportunity"></mc-icon>
                    <span><?=i::__('Minhas oportunidades')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <mc-icon name="opportunity"></mc-icon>
                    <span><?=i::__('Prestações de contas')?></span>
                </a>
            </li>
        </ul>
        <h3><?=i::__('Outras opções')?></h3>
        <ul>
            <li>
                <a href="#">
                    <mc-icon name="network"></mc-icon>
                    <span><?=i::__('Vincular cadastro')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <mc-icon name="app"></mc-icon>
                    <span><?=i::__('Meus apps')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <mc-icon name="settings"></mc-icon>
                    <span><?=i::__('Configuração de conta')?></span>
                </a>
            </li>
        </ul>
    </nav>
</div>

