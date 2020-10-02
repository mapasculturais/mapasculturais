<?php
$this->layout = 'panel';
$label = \MapasCulturais\i::__("Adicionar novo evento");
?>
<div class="panel-list panel-main-content">
    
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>
		<h2><?php \MapasCulturais\i::_e("Meus eventos"); ?></h2>
        <div class="btn btn-default add"> <?php $this->renderModalFor('event', false, $label); ?> </div>
	    <?php $this->applyTemplateHook('panel-header','end') ?>
    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>
	
	
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Ativos");?> (<?php echo $meta->count; ?>)</a></li>
        <li><a href="#permitido" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Concedidos");?> (<?php echo count($app->user->hasControlEvents);?>)</a></li>
        <li><a href="#rascunhos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Rascunhos");?> (<?php echo count($draft); ?>)</a></li>
        <li><a href="#lixeira" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Lixeira");?> (<?php echo count($trashed); ?>)</a></li>
        <li><a href="#arquivo" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Arquivo");?> (<?php echo count($app->user->archivedEvents);?>)</a></li>
    </ul>
    <div id="ativos">

        <?php $this->part('panel-search', ['meta' => $meta, 'search_entity' => 'event']); ?>

        <?php foreach($enabled as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$enabled): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum evento cadastrado.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($draft as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$draft): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum rascunho de evento.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($trashed as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$trashed): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum evento na lixeira.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
		<?php foreach($app->user->archivedEvents as $entity): ?>
            <?php $this->part('panel-event', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->archivedEvents): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum evento arquivado.");?></div>
        <?php endif; ?>
    </div>
    <!-- #arquivo-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlEvents as $entity): ?>
			<?php $this->part('panel-event', array('entity' => $entity, 'only_edit_button' => true)); ?>
		<?php endforeach; ?>
		<?php if(!$app->user->hasControlEvents): ?>
			<div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum evento liberado.");?></div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
</div>
