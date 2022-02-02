<?php
use MapasCulturais\i;
$this->import('tabs');

$profile = $app->user->profile;
?>

<div class="panel__row">
    <h1><?= i::__('Painel de controle') ?></h1>
    <h2><?= sprintf(i::__('Olá, %s'), $profile->name) ?></h2>
</div>

<?php $this->applyTemplateHook('tabs', 'before') ?>
<tabs>
    <?php $this->applyTemplateHook('tabs', 'begin') ?>
    <tab cache key="main" label="<?= i::__('Principal') ?>" slug="main">
        <h3><?= i::__('Acesso Rápido') ?></h3>
    </tab>
    <tab cache key="secondary" icon="mdi:star" label="<?= i::__('Secundária') ?>" slug="secondary">
        <h3><?= i::__('Conteúdo Secundário') ?></h3>
    </tab>
    <?php $this->applyTemplateHook('tabs', 'end') ?>
</tabs>
<?php $this->applyTemplateHook('tabs', 'after') ?>