<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2>Meus SaaS</h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('saas', 'create') ?>">Adicionar novo saas</a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos">Ativos</a></li>
        <li><a href="#rascunhos">Rascunhos</a></li>
        <li><a href="#lixeira">Lixeira</a></li>
    </ul>
    <div id="ativos">
        <?php foreach($user->enabledSaaS as $entity): ?>
            <?php $this->part('panel-saas', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->enabledSaaS): ?>
            <div class="alert info">Você não possui nenhum SaaS.</div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <div id="rascunhos">
        <?php foreach($user->draftSaaS as $entity): ?>
            <?php $this->part('panel-saas', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->draftSaaS): ?>
            <div class="alert info">Você não possui nenhum rascunho de saas.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($user->trashedSaaS as $entity): ?>
            <?php $this->part('panel-saas', array('entity' => $entity)); ?>
        <?php endforeach; ?>
        <?php if(!$user->trashedSaaS): ?>
            <div class="alert info">Você não possui nenhum saas na lixeira.</div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
