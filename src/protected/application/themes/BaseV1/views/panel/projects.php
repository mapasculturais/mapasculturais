<?php
$this->layout = 'panel';
$label = \MapasCulturais\i::__("Adicionar novo projeto");
?>
<div class="panel-list panel-main-content">
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>
		<h2><?php \MapasCulturais\i::_e("Meus projetos");?></h2>
        <div class="btn btn-default add"> <?php $this->renderModalFor('project', false, $label); ?> </div>

        <?php $this->applyTemplateHook('panel-header','end') ?>
    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>

    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Ativos");?> (<?php echo count($user->enabledProjects); ?>)</a></li>
        <li><a href="#permitido" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Concedidos");?> (<?php echo count($user->hasControlProjects); ?>)</a></li>
        <li><a href="#rascunhos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Rascunhos");?> (<?php echo count($user->draftProjects); ?>)</a></li>
        <li><a href="#lixeira" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Lixeira");?> (<?php echo count($user->trashedProjects); ?>)</a></li>
        <li><a href="#arquivo" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Arquivo");?> (<?php echo count($user->archivedProjects); ?>)</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledProjects as $entity): ?>
            <?php $this->part('panel-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledProjects): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum projeto.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($user->draftProjects as $entity): ?>
            <?php $this->part('panel-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftProjects): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum rascunho de projeto.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($user->trashedProjects as $entity): ?>
            <?php $this->part('panel-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->trashedProjects): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum projeto na lixeira.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
        <?php foreach($user->archivedProjects as $entity): ?>
            <?php $this->part('panel-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->archivedProjects): ?>
            <div class="alert info">Você não possui nenhum projeto arquivado.</div>
        <?php endif; ?>
    </div>
    <!-- #arquivo-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlProjects as $entity): ?>
			<?php $this->part('panel-project', array('entity' => $entity, 'only_edit_button' => true)); ?>
		<?php endforeach; ?>
		<?php if(!$user->hasControlProjects): ?>
			<div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum projeto liberado."); ?></div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
</div>
