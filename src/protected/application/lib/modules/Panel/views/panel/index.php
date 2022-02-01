<?php
use MapasCulturais\i;
$this->import('tabs');

$profile = $app->user->profile;
?>

<h1><?= i::__('Painel de controle') ?></h1>
<h2><?= sprintf(i::__('Olá, %s'), $profile->name) ?></h2>

<?php $this->applyTemplateHook('tabs', 'before') ?>
<tabs>
    <?php $this->applyTemplateHook('tabs', 'begin') ?>
    <tab label="<?= i::__('Principal') ?>" cached key="main" slug="main">
        <h3><?= i::__('Acesso Rápido') ?></h3>
    </tab>
    <tab label="<?= i::__('Secundária') ?>" cached key="secondary" slug="secondary">
        <h3><?= i::__('Conteúdo Secundário') ?></h3>
    </tab>
    <?php $this->applyTemplateHook('tabs', 'end') ?>
</tabs>
<?php $this->applyTemplateHook('tabs', 'after') ?>