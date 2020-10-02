<?php
$this->layout = 'panel';
$app = \MapasCulturais\App::i();
$opportunitiesToEvaluate = $user->opportunitiesCanBeEvaluated;
?>
<div class="panel-list panel-main-content">
    
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>
		<h2><?php \MapasCulturais\i::_e("Minhas oportunidades");?></h2>

        <?php $this->applyTemplateHook('panel-header','end') ?>
    </header>
    <?php $this->applyTemplateHook('panel-header','after'); ?>

    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Ativos");?> (<?php echo count($user->enabledOpportunities); ?>)</a></li>
        <li><a href="#permitido" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Concedidos");?> (<?php echo count($user->hasControlOpportunities); ?>)</a></li>
        <li><a href="#rascunhos" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Rascunhos");?> (<?php echo count($user->draftOpportunities); ?>)</a></li>
        <li><a href="#lixeira" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Lixeira");?> (<?php echo count($user->trashedOpportunities); ?>)</a></li>
        <li><a href="#arquivo" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Arquivo");?> (<?php echo count($user->archivedOpportunities); ?>)</a></li>
        <li><a href="#avaliacoes" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Avaliações");?> (<?php echo count($opportunitiesToEvaluate); ?>)</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledOpportunities as $entity): ?>
            <?php $this->part('panel-opportunity', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledOpportunities): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nehuma oportunidade.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($user->draftOpportunities as $entity): ?>
            <?php $this->part('panel-opportunity', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftOpportunities): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum rascunho de oportunidade.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($user->trashedOpportunities as $entity): ?>
            <?php $this->part('panel-opportunity', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->trashedOpportunities): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhuma oportunidade na lixeira.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
        <?php foreach($user->archivedOpportunities as $entity): ?>
            <?php $this->part('panel-opportunity', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->archivedOpportunities): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhuma oportunidade arquivada."); ?></div>
        <?php endif; ?>
    </div>
    <!-- #arquivo-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlOpportunities as $entity): ?>
			<?php $this->part('panel-opportunity', array('entity' => $entity)); ?>
		<?php endforeach; ?>
		<?php if(!$user->hasControlOpportunities): ?>
			<div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nehuma oportunidade liberada."); ?></div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
    <!-- #avaliar-->
    <div id="avaliacoes">
        <?php foreach($opportunitiesToEvaluate as $entity): ?>
            <?php $this->part('panel-evaluation', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$opportunitiesToEvaluate): ?>
            <div class="alert info">
                <?php // translators: %s é o nome do tipo de entidade Oportunidade. (ex: oportunidade) ?>
                <?php printf( \MapasCulturais\i::__("Você não possui nenhuma %s liberada para avaliação."), $this->dict('entities: opportunity', false) ); ?></div>
        <?php endif; ?>
    </div>
    <!-- #avaliar-->
</div>
