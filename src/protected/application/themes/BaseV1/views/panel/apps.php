<?php
$this->layout = 'panel'
?>
<div class="panel-list panel-main-content">
	<header class="panel-header clearfix">
		<h2><?php \MapasCulturais\i::_e("Meus apps");?></h2>
		<a class="btn btn-default add" href="<?php echo $app->createUrl('app', 'create'); ?>"><?php \MapasCulturais\i::_e("Adicionar novo app");?></a>
	</header>
    <ul class="abas clearfix clear">
        <li class="active"><a href="#ativos"><?php \MapasCulturais\i::_e("Ativos");?></a></li>
        <li><a href="#lixeira"><?php \MapasCulturais\i::_e("Lixeira");?></a></li>
    </ul>
    <div id="ativos">
        <?php foreach($enabledApps as $userApp): ?>
            <?php $this->part('panel-app', array('entity' => $userApp)); ?>
        <?php endforeach; ?>
        <?php if(!$enabledApps): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum app cadastrado.");?></div>
        <?php endif; ?>
    </div>
    <!-- #ativos-->
    <!-- #lixeira-->
    <div id="lixeira">
        <?php foreach($thrashedApps as $userApp): ?>
            <?php $this->part('panel-app', array('entity' => $userApp)); ?>
        <?php endforeach; ?>
        <?php if(!$thrashedApps): ?>
            <div class="alert info"><?php \MapasCulturais\i::_e("Você não possui nenhum app na lixeira.");?></div>
        <?php endif; ?>
    </div>
    <!-- #lixeira-->
</div>
