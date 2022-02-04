<?php
use MapasCulturais\i;
?>
<div class="panel-sidebar">
    <h2>
        <button class="button panel-sidebar__toggle" @click="open = !open">
            <iconify icon="mdi:chevron-up" v-show="open"></iconify>
            <iconify icon="mdi:chevron-down" v-show="!open"></iconify>
            <span><?=i::__('Menu do painel')?></span>
        </button>
    </h2>
    <nav :class="{ 'is-open': open }">
        <ul>
            <li>
                <a href="#">
                    <iconify icon="mdi:view-dashboard-outline"></iconify>
                    <span><?=i::__('Visão geral')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:account-multiple-outline"></iconify>
                    <span><?=i::__('Meus agentes')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:domain"></iconify>
                    <span><?=i::__('Meus espaços')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:calendar-month"></iconify>
                    <span><?=i::__('Meus eventos')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:clipboard-text-outline"></iconify>
                    <span><?=i::__('Meus projetos')?></span>
                </a>
            </li>
        </ul>
        <h3><?=i::__('Editais e oportunidades')?></h3>
        <ul>
            <li>
                <a href="#">
                    <iconify icon="mdi:lightbulb-on-outline"></iconify>
                    <span><?=i::__('Minhas inscrições')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:lightbulb-on-outline"></iconify>
                    <span><?=i::__('Minhas oportunidades')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:lightbulb-on-outline"></iconify>
                    <span><?=i::__('Prestações de contas')?></span>
                </a>
            </li>
        </ul>
        <h3><?=i::__('Outras opções')?></h3>
        <ul>
            <li>
                <a href="#">
                    <iconify icon="grommet-icons:connect" style="height: 1.25em"></iconify>
                    <span><?=i::__('Vincular cadastro')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:puzzle-outline"></iconify>
                    <span><?=i::__('Meus apps')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <iconify icon="mdi:cog-outline"></iconify>
                    <span><?=i::__('Configuração de conta')?></span>
                </a>
            </li>
        </ul>
    </nav>
</div>

