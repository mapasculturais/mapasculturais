<?php 
use MapasCulturais\i;
$this->import('theme-logo');
$config = $app->config['social-media'];
?>
<div class="main-footer">

    <div class="main-footer__logo">

        <div class="main-footer__logo--img">
            <theme-logo title="mapa cultural" subtitle="do Pará"></theme-logo>
        </div>

        <div class="main-footer__logo--share">
            <?php foreach($config as $conf): ?>
                <a target="_blank" href="<?= $conf['link'] ?>"><mc-icon name='<?= $conf['title'] ?>'></mc-icon></a>
            <?php endforeach; ?>
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
            <?php if (!($app->user->is('guest'))): ?>
            <li>
                <a href="<?= $app->createUrl('auth', 'logout') ?>">Sair</a>
            </li>
            <?php endif; ?>
        </ul>

        <ul class="main-footer__links--group">
           <?php var_dump($app->config['module.LGPD']); ?>

            <?php foreach($app->config['module.LGPD'] as $slug => $cfg): ?>

            <li>
                <a href="<?= $app->createUrl('lgpd', $slug) ?>">Termos de Uso de Imagem<?= $cfg['image-use'] ?></a>
            </li>

            <?php endforeach ?>

            <?php foreach($app->config['module.LGPD'] as $slug => $cfg): ?>

                <li>
                    <a href="<?= $app->createUrl('lgpd', $slug) ?>">Termos de Uso<?= $cfg['terms-of-usage'] ?></a>
                </li>

            <?php endforeach ?>

            <?php foreach($app->config['module.LGPD'] as $slug => $cfg): ?>

                <li>
                    <a href="<?= $app->createUrl('lgpd', $slug) ?>">Política de Privacidade<?= $cfg['privacy-police'] ?></a>
                </li>

            <?php endforeach ?>
        </ul>

    </div>

    <div class="main-footer__reg">
        <div class="main-footer__reg--content">
            <p>
                plataforma criada pela comunidade <strong><mc-icon name="map"></mc-icon>mapas culturais</strong> e desenvolvida por <strong>hacklab<span style="color: red">/</span></strong>
            </p>
    
            <a class="link" href="https://github.com/mapasculturais"> 
                <?php i::_e("Conheça o repositório") ?> 
                <mc-icon name="github"></mc-icon>
            </a>
        </div>
    </div>
</div>