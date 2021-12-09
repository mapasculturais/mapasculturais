<?php
use MapasCulturais\i;

use MapasCulturais\Entities\Registration;
$this->layout = 'panel';
$has_drafts_registration = false;
$drafts = $app->repo('Registration')->findByUser($app->user, Registration::STATUS_DRAFT);
$sent = $app->repo('Registration')->findByUser($app->user, 'sent');
$app->applyHookBoundTo($this, 'panel(registration.panel):begin', [&$sent,&$drafts]);
?>
<div class="panel-list panel-main-content">
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>
        <h2><?php \MapasCulturais\i::_e("Minhas inscrições");?></h2>

        <?php $this->applyTemplateHook('panel-header','end') ?>
    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>

    <ul class="abas clearfix clear">
        <?php $this->part('tab', ['id' => 'ativos', 'label' => i::__("Rascunhos"), 'active' => true]) ?>
        <?php $this->part('tab', ['id' => 'enviadas', 'label' => i::__("Enviadas")]) ?>
    </ul>
    <div id="ativos">
        <?php foreach($drafts as $registration): ?>
        <?php if($registration->opportunity->isRegistrationOpen()){?>
            <?php $has_drafts_registration = true; ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php } ?>
        <?php endforeach; ?>
        <?php if(!$drafts || !$has_drafts_registration): ?>
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