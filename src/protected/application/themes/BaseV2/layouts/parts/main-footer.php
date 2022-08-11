<?php 
use MapasCulturais\i;
?>
<div class="main-footer">

    <div class="main-footer__logo">

        <div class="main-footer__logo--img">
            <?php $this->part('site-logo') ?>
        </div>

        <div class="main-footer__logo--share">
            <a><mc-icon name="facebook"></mc-icon></a>
            <a><mc-icon name="twitter"></mc-icon></a>
            <a><mc-icon name="vimeo"></mc-icon></a>
            <a><mc-icon name="youtube"></mc-icon></a>
            
        </div>
    </div>

    <div class="main-footer__links">
        
        <ul class="main-footer__links--group">
            <li>
                <a>Acesse</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('search', 'opportunities') ?>"> 
                    <mc-icon name="opportunity"></mc-icon> <?php i::_e('editais e oportunidades'); ?>
                </a>
            </li>
            <li>
                <a href="<?= $app->createUrl('search', 'events') ?>"> 
                    <mc-icon name="event"></mc-icon>  <?php i::_e('eventos'); ?>
                </a>
            </li>
            <li>
                <a href="<?= $app->createUrl('search', 'agents') ?>"> 
                    <mc-icon name="agent"></mc-icon>  <?php i::_e('agentes'); ?>
                </a>
            </li>
            <li>
                <a href="<?= $app->createUrl('search', 'spaces') ?>"> 
                    <mc-icon name="space"></mc-icon>  <?php i::_e('espaços'); ?>
                </a>
            </li>
            <li>
                <a href="<?= $app->createUrl('search', 'projects') ?>"> 
                    <mc-icon name="project"></mc-icon>  <?php i::_e('projetos'); ?>
                </a>
            </li>
        </ul>
        
        <ul class="main-footer__links--group">
            <li>
                <a href="<?= $app->createUrl('panel', 'index') ?>">Painel</a>
            </li>
            <li>
                <a  href="<?= $app->createUrl('panel', 'opportunities') ?>">Editais e oportunidades</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('panel', 'events') ?>">Meus eventos</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('panel', 'agents') ?>">Meus agentes</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('panel', 'spaces') ?>">Meus espaços</a>
            </li>
            <li>
                <a href="<?= $app->createUrl('auth', 'logout') ?>">Sair</a>
            </li>
        </ul>

        <ul class="main-footer__links--group">
            <li>
                <a>Ajuda e Privacidade</a>
            </li>
            <li>
                <a>Como utilizar o mapa?</a>
            </li>
            <li>
                <a>Dúvidas frequentes (FAQ)</a>
            </li>
            <li>
                <a>Termos de uso</a>
            </li>
            <li>
                <a>Política de privacidade</a>
            </li>
            <li>
                <a>Autorização de uso de imagem</a>
            </li>
        </ul>

    </div>

    <div class="main-footer__reg">
        <div class="main-footer__reg--content">
            <p>
                plataforma criada pela comunidade <strong>mapas culturais</strong> e desenvolvida por <strong>hacklab<span style="color: red">/</span></strong>
            </p>
    
            <a class="link" href="https://github.com/mapasculturais"> 
                <?php i::_e("Conheça o repositório") ?> 
                <mc-icon name="github"></mc-icon>
            </a>
        </div>
    </div>
</div>