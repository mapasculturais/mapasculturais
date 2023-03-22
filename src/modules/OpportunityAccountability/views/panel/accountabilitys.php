<?php
use MapasCulturais\Entities\Registration;
$this->layout = 'panel';

$drafts = $app->repo('Registration')->findByUser($app->user, Registration::STATUS_DRAFT);
$sent = $app->repo('Registration')->findByUser($app->user, 'sent');
$app->applyHookBoundTo($this, 'panel(accountability.panel):begin', [&$sent,&$drafts]);
$showMessage = true;
?>

<div class="panel-list panel-main-content">
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>
        <h2><?php \MapasCulturais\i::_e("Minhas prestações de contas");?></h2>

        <?php $this->applyTemplateHook('panel-header','end') ?>
    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>

    <ul class="abas clearfix clear">
            <li class="active"><a href="#ativos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Rascunhos");?></a></li>
            <li><a href="#enviadas" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Enviadas");?></a></li>
    </ul>
    <div id="ativos">
        <?php foreach($drafts as $registration): ?>
            <?php if($registration->opportunity->isRegistrationOpen()){?>
                <?php $showMessage = false;?>
                <?php $this->part('panel-registration', array('registration' => $registration)); ?>
            <?php } ?>
        <?php endforeach; ?>
        <?php if(!$drafts || $showMessage): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum rascunho de prestação de conta.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="enviadas">
        <?php foreach($sent as $registration): ?>
            <?php $this->part('panel-registration', array('registration' => $registration)); ?>
        <?php endforeach; ?>
        <?php if(!$sent): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não enviou nenhuma prestação de conta.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>