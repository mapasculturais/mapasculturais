<?php
use MapasCulturais\Entities\Registration;
$this->layout = 'panel';

$drafts = $app->repo('Registration')->findByUser($app->user, Registration::STATUS_DRAFT);
$sent = $app->repo('Registration')->findByUser($app->user, 'sent');
?>
<div class="lista-sem-thumb main-content">
    <header class="header-do-painel clearfix">
            <h2>Minhas inscrições</h2>
    </header>
    <ul class="abas clearfix clear">
        <?php if($drafts): ?>
            <li class="active"><a href="#ativos">Rascunhos</a></li>
        <?php endif; ?>
        <?php if($sent): ?>
            <li><a href="#enviadas">Enviadas</a></li>
        <?php endif; ?>
    </ul>
    <div id="ativos">
        <?php foreach($drafts as $registration): ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #ativos-->
    <div id="enviadas">
        <?php foreach($sent as $registration): ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php endforeach; ?>
    </div>
    <!-- #lixeira-->
</div>