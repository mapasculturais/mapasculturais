<?php 
use MapasCulturais\i;
?>
<h2><span><?=i::__('Menu do painel')?></h2>
<nav>
    <ul>
        <li>
            <a href="#">
                <iconify icon="lucide:layout-dashboard"></iconify>
                <span><?=i::__('Visão geral')?></span>
            </a>
        </li>
        <li>
            <a href="#">
                <iconify icon="fa-solid:user-friends"></iconify>
                <span><?=i::__('Meus agentes')?></span>
            </a>
        </li>
        <li>
            <a href="#">
                <iconify icon="clarity:building-line"></iconify>
                <span><?=i::__('Meus espaços')?></span>
            </a>
        </li>
        <li>
            <a href="#">
                <iconify icon="ant-design:calendar"></iconify>
                <span><?=i::__('Meus eventos')?></span>
            </a>
        </li>
        <li>
            <a href="#">
                <iconify icon="ri:file-list-2-line"></iconify>
                <span><?=i::__('Meus projetos')?></span>
            </a>
        </li>
    </ul>
</nav>