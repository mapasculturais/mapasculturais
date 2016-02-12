<?php
use MapasCulturais\Entities\Registration;
$this->layout = 'panel';

$drafts = $app->repo('Registration')->findByUser($app->user, Registration::STATUS_DRAFT);
$sent = $app->repo('Registration')->findByUser($app->user, 'sent');
?>
<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
            <h2>Mis inscripciones</h2>
    </header>
    <ul class="abas clearfix clear">
            <li class="active"><a href="#ativos">Borradores</a></li>
            <li><a href="#enviadas">Enviadas</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($drafts as $registration): ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php endforeach; ?>
        <?php if(!$drafts): ?>
            <div class="alert info">Usted no posee ningún borrador de inscripción.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="enviadas">
        <?php foreach($sent as $registration): ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php endforeach; ?>
        <?php if(!$sent): ?>
            <div class="alert info">Usted no envió ninguna inscripción.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>