<?php
$this->jsObject['spinner'] = $this->asset('img/spinner_192.gif', false);

$app = \MapasCulturais\App::i();
$em = $app->em;

?>
<section id="home-watermark">

</section>
<?php $this->applyTemplateHook('home-search','before'); ?>
<?php $this->part('home-search'); ?>
<?php $this->applyTemplateHook('home-search','after'); ?>

<?php $this->applyTemplateHook('home-events','before'); ?>
<?php $this->part('home-events'); ?>
<?php $this->applyTemplateHook('home-events','after'); ?>

<?php $this->applyTemplateHook('home-agents','before'); ?>
<?php $this->part('home-agents'); ?>
<?php $this->applyTemplateHook('home-agents','after'); ?>

<?php $this->applyTemplateHook('home-spaces','before'); ?>
<?php $this->part('home-spaces'); ?>
<?php $this->applyTemplateHook('home-spaces','after'); ?>

<?php $this->applyTemplateHook('home-projects','before'); ?>
<?php $this->part('home-projects'); ?>
<?php $this->applyTemplateHook('home-projects','after'); ?>

<?php $this->applyTemplateHook('home-opportunities','before'); ?>
<?php $this->part('home-opportunities'); ?>
<?php $this->applyTemplateHook('home-opportunities','after'); ?>

<?php $this->applyTemplateHook('home-developers','before'); ?>
<?php $this->part('home-developers'); ?>
<?php $this->applyTemplateHook('home-developers','after'); ?>

<?php $this->applyTemplateHook('home-nav','before'); ?>
<?php $this->part('home-nav'); ?>
<?php $this->applyTemplateHook('home-nav','after'); ?>