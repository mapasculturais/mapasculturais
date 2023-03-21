<?php
use MapasCulturais\i;

$this->layout = 'panel';
$app = \MapasCulturais\App::i();
$label = \MapasCulturais\i::__("Adicionar ") . $this->dict('entities: new space', false);

$ativos_num = $meta->count;
$permitido_num = count($app->user->hasControlSpaces);
$rascunhos_num = count($draft);
$lixeira_num = count($trashed);
$arquivo_num = count($app->user->archivedSpaces);
?>
<div class="panel-list panel-main-content">
    
    <?php $this->applyTemplateHook('panel-header','before'); ?>
	<header class="panel-header clearfix">
        <?php $this->applyTemplateHook('panel-header','begin'); ?>
        <h2><?php $this->dict('entities: My spaces') ?></h2>
        <div class="btn btn-default add"> <?php $this->renderModalFor('space', false, $label); ?> </div>
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

        <?php $this->part('panel-search', ['meta' => $meta, 'search_entity' => 'space']); ?>

        <?php foreach($enabled as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$enabled): ?>
            <div class="alert info"><?php $this->dict('entities: no registered spaces') ?>.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($draft as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$draft): ?>
            <div class="alert info"><?php printf(\MapasCulturais\i::__("Você não possui nenhum rascunho de %s"), $this->dict('entities: space', false));?>.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($trashed as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$trashed): ?>
            <div class="alert info"><?php printf(\MapasCulturais\i::__('%s na lixeira.'), $this->dict('entities: no spaces')); ?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
	<!-- #arquivo-->
    <div id="arquivo">
		<?php foreach($app->user->archivedSpaces as $entity): ?>
            <?php $this->part('panel-space', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->archivedSpaces): ?>
            <div class="alert info"><?php printf(\MapasCulturais\i::__('%s arquivado.'), $this->dict('entities: no spaces')); ?></div>
        <?php endif; ?>
    </div>
    <!-- #arquivo-->
	<!-- #permitido-->
	<div id="permitido">
		<?php foreach($app->user->hasControlSpaces as $entity): ?>
			<?php $this->part('panel-space', array('entity' => $entity, 'only_edit_button' => true)); ?>
		<?php endforeach; ?>
		<?php if(!$app->user->hasControlSpaces): ?>
			<div class="alert info"><?php printf(\MapasCulturais\i::__('%s liberado.'), $this->dict('entities: no spaces')); ?></div>
		<?php endif; ?>
	</div>
	<!-- #permitido-->
</div>
