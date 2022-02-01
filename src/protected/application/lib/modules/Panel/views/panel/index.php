<?php
use MapasCulturais\i;
$this->import('panel tabs');

$profile = $app->user->profile;
?>

<panel>
<h1><?= i::__('Painel de controle') ?></h1>
<h2><?= sprintf(i::__('Olá, %s'), $profile->name) ?></h2>

<?php $this->applyTemplateHook('tabs', 'before') ?>
<tabs>
    <?php $this->applyTemplateHook('tabs', 'begin') ?>
    <tab name="<?= i::__('Principal') ?>">
        <h3><?= i::__('Acesso Rápido') ?></h3>
    </tab>
    <?php $this->applyTemplateHook('tabs', 'end') ?>
</tabs>
<?php $this->applyTemplateHook('tabs', 'after') ?>
</panel>