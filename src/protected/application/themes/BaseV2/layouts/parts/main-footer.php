<?php

use MapasCulturais\i;

$this->import('theme-logo');
$config = $app->config['social-media'];
?>
<div class="main-footer">

    <div class="main-footer__content">
        <div class="main-footer__content--logo">
    
            <div class="main-footer__content--logo-img">
                <theme-logo title="<?php i::_e('mapa cultural'); ?>" subtitle="<?php i::_e('do Pará'); ?>"></theme-logo>
            </div>
    
            <div class="main-footer__content--logo-share">
                <?php foreach ($config as $conf) : ?>
                    <a target="_blank" href="<?= $conf['link'] ?>">
                        <mc-icon name='<?= $conf['title'] ?>'></mc-icon>
                    </a>
                <?php endforeach; ?>
            </div>
        </div>
    
        <div class="main-footer__content--links">
    
            <ul class="main-footer__content--links-group">
                <li>
                    <a><?php i::_e("Acesse"); ?></a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'opportunities') ?>">
                        <mc-icon name="opportunity"></mc-icon> <?php i::_e('editais e oportunidades'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'events') ?>">
                        <mc-icon name="event"></mc-icon> <?php i::_e('eventos'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'agents') ?>">
                        <mc-icon name="agent"></mc-icon> <?php i::_e('agentes'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'spaces') ?>">
                        <mc-icon name="space"></mc-icon> <?php i::_e('espaços'); ?>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'projects') ?>">
                        <mc-icon name="project"></mc-icon> <?php i::_e('projetos'); ?>
                    </a>
                </li>
            </ul>
    
            <ul class="main-footer__content--links-group">
                <li>
                    <a href="<?= $app->createUrl('panel', 'index') ?>"><?php i::_e('Painel'); ?></a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('panel', 'opportunities') ?>"><?php i::_e('Editais e oportunidades'); ?></a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('panel', 'events') ?>"><?php i::_e('Meus eventos'); ?></a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('panel', 'agents') ?>"><?php i::_e('Meus agentes'); ?></a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('panel', 'spaces') ?>"><?php i::_e('Meus espaços'); ?></a>
                </li>
                <?php if (!($app->user->is('guest'))) : ?>
                    <li>
                        <a href="<?= $app->createUrl('auth', 'logout') ?>">Sair</a>
                    </li>
                <?php endif; ?>
            </ul>
    
            <?php if (count($app->config['module.LGPD']) > 0): ?>
                <ul class="main-footer__content--links-group">
                    <li>
                        <a><?php i::_e('Ajuda e Privacidade'); ?></a>
                    </li>
                    
                    <?php foreach ($app->config['module.LGPD'] as $slug => $cfg) : ?>
                        <li>
                            <a href="<?= $app->createUrl('lgpd', $slug) ?>"><?= $cfg['title'] ?></a>
                        </li>
                    <?php endforeach ?>
                    
                </ul>
            <?php endif; ?>
    
        </div>
    
        <div class="main-footer__content--reg">
            <div class="main-footer__content--reg-content">
                <p>
                    <?php i::_e("plataforma criada pela comunidade") ?> 
                    <span class="mapas"> <mc-icon name="map"></mc-icon><?php i::_e("mapas culturais"); ?> </span> 
                    <?php i::_e("e desenvolvida por "); ?><strong>hacklab<span style="color: red">/</span></strong>
                </p>
    
                <a class="link" href="https://github.com/mapasculturais">
                    <?php i::_e("Conheça o repositório") ?>
                    <mc-icon name="github"></mc-icon>
                </a>
            </div>
        </div>        
    </div>
</div>