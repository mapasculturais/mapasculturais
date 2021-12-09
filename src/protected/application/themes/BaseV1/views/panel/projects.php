<?php
use MapasCulturais\i;

$this->layout = 'panel';
$label = \MapasCulturais\i::__("Adicionar novo projeto");

$ativos_num = count($user->enabledProjects);
$permitido_num = count($user->hasControlProjects);
$rascunhos_num = count($user->draftProjects);
$lixeira_num = count($user->trashedProjects);
$arquivo_num = count($user->archivedProjects);
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
        <?php $this->part('tab', ['id' => 'ativos', 'label' => i::__("Ativos") . " ($ativos_num)", 'active' => true]) ?>
        <?php $this->part('tab', ['id' => 'permitido', 'label' => i::__("Concedidos") . " ($permitido_num)"]) ?>
        <?php $this->part('tab', ['id' => 'rascunhos', 'label' => i::__("Rascunhos") . " ($rascunhos_num)"]) ?>
        <?php $this->part('tab', ['id' => 'lixeira', 'label' => i::__("Lixeira") . " ($lixeira_num)"]) ?>
        <?php $this->part('tab', ['id' => 'arquivo', 'label' => i::__("Arquivo") . " ($arquivo_num)"]) ?>
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
