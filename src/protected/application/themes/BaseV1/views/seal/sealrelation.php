<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($relation);

if($this->isEditable()){
	$this->addEntityTypesToJs($relation);
}

$this->includeMapAssets();

$this->includeAngularEntityAssets($relation);

$entity = $relation->seal;

?>
<article class="main-content seal">
    <header class="main-content-header">
    <?php
    	if ($header = $entity->getFile('header')){
		    $style = "background-image: url({$header->transform('header')->url});";
		} else {
		    $style = "";
		} ?>
        <?php $this->applyTemplateHook('header-image','before'); ?>
        <div class="header-image js-imagem-do-header" style="<?php echo $style ?>">
		<?php if(!$app->user->is('guest') && $app->isEnabled('seals') && ($app->user->is('superAdmin')
                    || $app->user->is('admin') || $app->user->profile->id == $relation->agent->id)) {?>
			<a class="btn btn-default js-open-editbox" href="<?php echo $app->createUrl('seal','printsealrelation',[$relation->id]);?>"><?php \MapasCulturais\i::_e("Imprimir Certificado");?></a>
		<?php } ?>
	</div>
	<?php $this->applyTemplateHook('header-image','after'); ?>

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>

        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>

            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--seal.png']); ?>

			<?php $this->applyTemplateHook('name','before'); ?>
			<h2><span class="js-editable" data-edit="name" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>"><a href="<?php echo $app->createUrl('seal', 'single', ['id' => $entity->id])?>"><?php echo $entity->name; ?></a></span></h2>
			<?php $this->applyTemplateHook('name','after'); ?>

            <?php $this->applyTemplateHook('header-content','end'); ?>
        </div>
        <!--.header-content-->
        <?php $this->applyTemplateHook('header-content','after'); ?>
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>

        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <div class="ficha-spcultura">
                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta");?>" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
            </div>
            <!--.ficha-spcultura-->

            <?php if ( $entity->longDescription ): ?>
                <h3><?php \MapasCulturais\i::_e("Descrição");?></h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição do Selo");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição do selo");?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>
            <!--.descricao-->
        </div>
        <!-- #sobre -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

	<?php $this->part('owner', array('entity' => $relation, 'owner' => $relation->owner_relation)); ?>
</article>
<div class="sidebar-left sidebar seal">

</div>
<div class="sidebar seal sidebar-right">

</div>
