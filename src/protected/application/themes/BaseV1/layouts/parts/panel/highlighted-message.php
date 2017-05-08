<?php $this->applyTemplateHook('highlighted-message','before'); ?>
    <p class="highlighted-message">
        <?php $this->applyTemplateHook('highlighted-message','begin'); ?>

        <?php \MapasCulturais\i::_e("Olá");?>, <a href="<?php echo $app->user->profile->singleUrl ?>"><?php echo $app->user->profile->name ?></a>, <?php \MapasCulturais\i::_e("bem-vindo ao painel do");?> <?php $this->dict('site: name'); ?>!

        <?php $this->applyTemplateHook('highlighted-message','end'); ?>
    </p>
<?php $this->applyTemplateHook('highlighted-message','after'); ?>
