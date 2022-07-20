<?php
use MapasCulturais\i;

$this->layout = 'panel';
$label = \MapasCulturais\i::__("Adicionar novo agente");

$ativos_num = count($user->enabledAgents);
$permitido_num = count($app->user->hasControlAgents);
$rascunhos_num = count($user->draftAgents);
$lixeira_num = count($user->trashedAgents);
$arquivo_num = count($app->user->archivedAgents);
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
        <?php $this->part('tab', ['id' => 'ativos', 'label' => i::__("Ativos") . " ($ativos_num)", 'active' => true]) ?>
        <?php $this->part('tab', ['id' => 'permitido', 'label' => i::__("Concedidos") . " ($permitido_num)"]) ?>
        <?php $this->part('tab', ['id' => 'rascunhos', 'label' => i::__("Rascunhos") . " ($rascunhos_num)"]) ?>
        <?php $this->part('tab', ['id' => 'lixeira', 'label' => i::__("Lixeira") . " ($lixeira_num)"]) ?>
        <?php $this->part('tab', ['id' => 'arquivo', 'label' => i::__("Arquivo") . " ($arquivo_num)"]) ?>
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
