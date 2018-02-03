<?php
use MapasCulturais\Entities\Registration;
$this->layout = 'panel';

$drafts = $app->repo('Registration')->findByUser($app->user, Registration::STATUS_DRAFT);
$sent = $app->repo('Registration')->findByUser($app->user, 'sent');
?>
<div class="panel-list panel-main-content">
    <header class="panel-header clearfix">
            <h2><?php \MapasCulturais\i::_e("Minhas inscrições");?></h2>
    </header>
    <ul class="abas clearfix clear">
            <li class="active"><a href="#ativos"><?php \MapasCulturais\i::_e("Rascunhos");?></a></li>
            <li><a href="#enviadas"><?php \MapasCulturais\i::_e("Enviadas");?></a></li>
    </ul>
    <div id="ativos">
        <?php foreach($drafts as $registration): ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php endforeach; ?>
        <?php if(!$drafts): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum rascunho de inscrição.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="enviadas">
        <?php foreach($sent as $registration): ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php endforeach; ?>
        <?php if(!$sent): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não enviou nenhuma inscrição.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>