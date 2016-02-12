<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2>Mis agentes</h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('agent', 'create'); ?>">Agregar nuevo agente</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Activos</a></li>
        <li><a href="#rascunhos">Borradores</a></li>
        <li><a href="#lixeira">Papelera</a></li>
    </ul>
    <div id="ativos">
        <?php $this->part('panel-agent', array('entity' => $app->user->profile)); ?>
        <?php foreach($user->enabledAgents as $entity): if($app->user->profile->equals($entity)) continue;?>
            <?php $this->part('panel-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledAgents): ?>
            <div class="alert info">Usted no posee ningún agente registrado.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($app->user->draftAgents as $entity): ?>
            <?php $this->part('panel-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftAgents): ?>
            <div class="alert info">Usted no posee ningún borrador agente.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($app->user->trashedAgents as $entity): ?>
            <?php $this->part('panel-agent', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->trashedAgents): ?>
            <div class="alert info">Usted no posee ningún agente en la papelera.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
