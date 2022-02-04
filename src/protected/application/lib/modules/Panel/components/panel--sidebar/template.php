<?php
use MapasCulturais\i;
?>
<div class="panel-sidebar">
    <h2>
        <button class="panel-sidebar__toggle" @click="open = !open">
            <iconify icon="mdi:chevron-up" v-show="open"></iconify>
            <iconify icon="mdi:chevron-down" v-show="!open"></iconify>
            <span><?=i::__('Menu do painel')?></span>
        </button>
    </h2>
    <nav :class="{ 'is-open': open }">
        <ul>
            <li>
                <a href="#">
                    <!-- <iconify icon="lucide:layout-dashboard"></iconify> -->
                    <iconify icon="mdi:view-dashboard-outline"></iconify>
                    <span><?=i::__('Visão geral')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <!-- <iconify icon="fa-solid:user-friends"></iconify> -->
                    <iconify icon="mdi:account-multiple-outline"></iconify>
                    <span><?=i::__('Meus agentes')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <!-- <iconify icon="clarity:building-line"></iconify> -->
                    <iconify icon="mdi:domain"></iconify>
                    <span><?=i::__('Meus espaços')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <!-- <iconify icon="ant-design:calendar"></iconify> -->
                    <iconify icon="mdi:calendar-month"></iconify>
                    <span><?=i::__('Meus eventos')?></span>
                </a>
            </li>
            <li>
                <a href="#">
                    <!-- <iconify icon="ri:file-list-2-line"></iconify> -->
                    <iconify icon="mdi:clipboard-text-outline"></iconify>
                    <span><?=i::__('Meus projetos')?></span>
                </a>
            </li>
        </ul>
    </nav>
</div>

