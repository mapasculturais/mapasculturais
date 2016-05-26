<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2>Mis Proyectos</h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('project', 'create') ?>">Agregar nuevo proyecto</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Activos</a></li>
        <li><a href="#rascunhos">Borradores</a></li>
        <li><a href="#lixeira">Papelera</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledProjects as $entity): ?>
            <?php $this->part('panel-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledProjects): ?>
            <div class="alert info">Usted no posee ningún proyecto.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($user->draftProjects as $entity): ?>
            <?php $this->part('panel-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftProjects): ?>
            <div class="alert info">Usted no posee ningún borrador de proyecto.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($user->trashedProjects as $entity): ?>
            <?php $this->part('panel-project', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->trashedProjects): ?>
            <div class="alert info">Usted no posee ningún proyecto en la papelera.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
