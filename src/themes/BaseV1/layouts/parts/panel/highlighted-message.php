<?php $this->applyTemplateHook('highlighted-message','before'); ?>
    <p class="highlighted-message">
        <?php $this->applyTemplateHook('highlighted-message','begin'); ?>

        <?php $linkProfile = '<a rel="noopener noreferrer" href="' . $app->user->profile->singleUrl . '">' . htmlentities($app->user->profile->name) . '</a>'; ?>
        <?php printf(\MapasCulturais\i::__("Olá, %s, bem-vindo ao painel do %s!"), $linkProfile, $this->dict('site: name', false));?>

        <?php $this->applyTemplateHook('highlighted-message','end'); ?>
    </p>
<?php $this->applyTemplateHook('highlighted-message','after'); ?>