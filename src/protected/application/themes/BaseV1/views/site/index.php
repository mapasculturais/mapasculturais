<?php
$this->jsObject['spinner'] = $this->asset('img/spinner_192.gif', false);

$app = \MapasCulturais\App::i();
$em = $app->em;

?>
<section id="home-watermark">

</section>

<?php $this->part('home-search'); ?>

<?php $this->part('home-events'); ?>

<?php $this->part('home-agents'); ?>

<?php $this->part('home-spaces'); ?>

<?php $this->part('home-projects'); ?>

<?php $this->part('home-developers'); ?>

<?php $this->part('home-nav'); ?>
