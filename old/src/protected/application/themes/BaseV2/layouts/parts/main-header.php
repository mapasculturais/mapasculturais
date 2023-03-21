<?php
use MapasCulturais\App;
use MapasCulturais\i;
$this->import('
    messages 
    mc-header-menu
    mc-header-menu-user
    mc-icon
    theme-logo 
');

?>

<?php $this->applyTemplateHook('main-header', 'before') ?>
<header class="main-header" id="main-header">
    <?php $this->applyTemplateHook('main-header', 'begin') ?>

    <div class="main-header__content">

        <?php $this->applyTemplateHook('mc-header-menu', 'before') ?>
        <mc-header-menu>
            <?php $this->applyTemplateHook('mc-header-menu', 'begin') ?>

            <!-- Logo -->
            <template #logo>
                <theme-logo title="mapa cultural" subtitle="do Pará" href="<?= $app->createUrl('site', 'index') ?>"></theme-logo>
            </template>
            <!-- Menu principal -->
            <template #default>
                <li>
                    <a href="<?= $app->createUrl('site', 'index') ?>" class="mc-header-menu--item home">
                        <span class="icon"> <mc-icon name="home"></mc-icon> </span>
                        <p class="label"> <?php i::_e('Home') ?> </p>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'opportunities') ?>" class="mc-header-menu--item opportunity">
                        <span class="icon opportunity__bg-hover"> <mc-icon name="opportunity"></mc-icon> </span>
                        <p class="label"> <?php i::_e('Oportunidades') ?> </p>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'agents') ?>" class="mc-header-menu--item agent">
                        <span class="icon"> <mc-icon name="agent-2"> </span>
                        <p class="label"> <?php i::_e('Agentes') ?> </p>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'events') ?>" class="mc-header-menu--item event">
                        <span class="icon"> <mc-icon name="event"> </span>
                        <p class="label"> <?php i::_e('Eventos') ?> </p>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'spaces') ?>" class="mc-header-menu--item space">
                        <span class="icon"> <mc-icon name="space"> </span>
                        <p class="label"> <?php i::_e('Espaços') ?> </p>
                    </a>
                </li>
                <li>
                    <a href="<?= $app->createUrl('search', 'projects') ?>" class="mc-header-menu--item project">
                        <span class="icon"> <mc-icon name="project"> </span>
                        <p class="label"> <?php i::_e('Projetos') ?> </p>
                    </a>
                </li>
            </template>

            <?php $this->applyTemplateHook('mc-header-menu', 'end') ?>
        </mc-header-menu>
        <?php $this->applyTemplateHook('mc-header-menu', 'after') ?>


        <?php $this->applyTemplateHook('mc-header-menu-user', 'before') ?>
        <?php if ($app->user->is('guest')): ?>
            <!-- Botão login -->
            <a href="<?= $app->createUrl('auth') ?>" class="logIn">
                <?php i::_e('Entrar') ?>
            </a>
        <?php else: ?>
            <!-- Menu do usuário -->
            <mc-header-menu-user></mc-header-menu-user>
        <?php endif; ?>
        <?php $this->applyTemplateHook('mc-header-menu-user', 'after') ?>

    </div>

    <?php $this->applyTemplateHook('main-header', 'end') ?>
</header>
<?php $this->applyTemplateHook('main-header', 'after') ?>

<messages></messages>