<?php
$this->layout = 'panel';
$app = \MapasCulturais\App::i();
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php \MapasCulturais\i::_e("Minhas avaliações");?></h2>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativas"><?php \MapasCulturais\i::_e("Ativas");?> (<?php echo count($app->user->opportunitiesCanBeEvaluated); ?>)</a></li>
        <li><a href="#finalizadas"><?php \MapasCulturais\i::_e("Finalizadas");?> (<?php echo count($app->user->opportunitiesAlreadyEvaluated); ?>)</a></li>

    </ul>
    <!-- #ativas-->
    <div id="ativas">
        <?php foreach($app->user->opportunitiesCanBeEvaluated as $entity): ?>
            <?php $this->part('panel-evaluation', array('entity' => $entity, 'showRegistrationsButton' => true)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->opportunitiesCanBeEvaluated): ?>
            <div class="alert info">
                <?php // translators: %s é o nome do tipo de entidade Oportunidade. (ex: oportunidade) ?>
                <?php printf( \MapasCulturais\i::__("Você não possui nenhuma %s liberada para avaliação."), $this->dict('entities: opportunity', false) ); ?></div>
        <?php endif; ?>
    </div>
    <!-- #ativas-->

     <!-- #finalizadas-->
     <div id="finalizadas">
        <?php foreach($app->user->opportunitiesAlreadyEvaluated as $entity): ?>
            <?php $this->part('panel-evaluation', array('entity' => $entity, 'showRegistrationsButton' => false)); ?>
        <?php endforeach; ?>
        <?php if(!$app->user->opportunitiesAlreadyEvaluated): ?>
            <div class="alert info">
                <?php // translators: %s é o nome do tipo de entidade Oportunidade. (ex: oportunidade) ?>
                <?php printf( \MapasCulturais\i::__("Você não possui nenhuma %s finalizada."), $this->dict('entities: opportunity', false) ); ?></div>
        <?php endif; ?>
    </div>
    <!-- #finalizadas-->
</div>
