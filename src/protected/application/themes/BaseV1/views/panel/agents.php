<?php
$this->layout = 'panel';
$label = \MapasCulturais\i::__("Adicionar novo agente");
?>
<?php /*$this->part('singles/breadcrumb', ['entity' => $app->entity]); */?>
<div class="panel-list panel-main-content">
    
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>
        <h2><?php \MapasCulturais\i::_e("Meus agentes");?></h2>
        <div class="btn btn-default add"> <?php $this->renderModalFor('agent', false, $label); ?> </div>
        <?php $this->applyTemplateHook('panel-header','end') ?>
    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>

    <ul class="abas clearfix clear">

        <li class="active"><a href="#ativos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Ativos");?> (<?php echo count($user->enabledAgents); ?>)</a></li>
        <li><a href="#permitido" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Concedidos");?> (<?php echo count($app->user->hasControlAgents); ?>)</a></li>
        <li><a href="#rascunhos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Rascunhos");?> (<?php echo count($user->draftAgents); ?>)</a></li>
        <li><a href="#lixeira" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Lixeira");?> (<?php echo count($user->trashedAgents); ?>)</a></li>
        <li><a href="#arquivo" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Arquivo");?> (<?php echo count($app->user->archivedAgents);?>)</a></li>
    </ul>
    <div id="ativos">
		<?php $this->part('panel-agent', array('entity' => $app->user->profile)); ?>
        <?php foreach($user->enabledAgents as $entity): if($app->user->profile->equals($entity)) continue;?>
            <?php $this->part('panel-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledAgents): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum agente cadastrado.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($app->user->draftAgents as $entity): ?>
            <?php $this->part('panel-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftAgents): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum rascunho agente.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($app->user->trashedAgents as $entity): ?>
            <?php $this->part('panel-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->trashedAgents): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum agente na lixeira.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
        <?php foreach($app->user->archivedAgents as $entity): ?>
            <?php $this->part('panel-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->archivedAgents): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum agente arquivado.");?></div>
        <?php endif; ?>
    </div>
    <!-- #arquivo-->
	<!-- #permitido-->
    <div id="permitido">
		<?php foreach($app->user->hasControlAgents as $entity): ?>
			<?php $this->part('panel-agent', array('entity' => $entity, 'only_edit_button' => true)); ?>
		<?php endforeach; ?>
		<?php if(!$app->user->hasControlAgents): ?>
			<div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum agente liberado.");?></div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
</div>
