<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

// \dump($relation);
$this->addEntityToJs($relation);

if($this->isEditable()){
	$this->addEntityTypesToJs($relation);
}

$this->includeMapAssets();

$this->includeAngularEntityAssets($relation);

$entity = $relation->seal;


if ($header = $entity->getFile('header')){
    $style = "background-image: url({$header->transform('header')->url});";
} else {
    $style = "";
}
?>
<article class="main-content seal">
    <header class="main-content-header">
        <?php $this->applyTemplateHook('header-image','before'); ?>
        <div class="header-image js-imagem-do-header" style="<?php echo $style ?>">
            <?php if($relation->canUser('print')) :?>
                <a class="btn btn-default js-open-editbox" href="<?php echo $app->createUrl('seal','printsealrelation',[$relation->id]);?>"><?php \MapasCulturais\i::_e("Imprimir Certificado");?></a>
            <?php endif; ?>
	</div>
	<?php $this->applyTemplateHook('header-image','after'); ?>

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>

        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>

            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--seal.png']); ?>

			<?php $this->applyTemplateHook('name','before'); ?>
			<h2><span class="js-editable" data-edit="name" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>"><a href="<?php echo $app->createUrl('seal', 'single', ['id' => $entity->id])?>"><?php echo $entity->name; ?></a></span></h2>
			<?php $this->applyTemplateHook('name','after'); ?>

            <?php if(is_object($relation->validateDate)): ?>
                <?php   
                    $now = new \DateTime;
                    $diff = ($relation->validateDate->format("U") - $now->format("U"))/86400;
                ?>
                <div>
                    <span class="label">
                        <?php if($diff <= 0):?>
                            <?php \MapasCulturais\i::_e('Expirou em:'); ?>
                        <?php else:?>
                            <?php \MapasCulturais\i::_e('Válido em:'); ?>
                        <?php endif;?>
                    </span>
                    <span class="js-editable" data-edit="validateDate" data-original-title="Data de Validade" data-emptytext=""><?php echo $relation->validateDate->format("d/m/Y"); ?></span>
                    &nbsp;
                    <?php if($relation->seal->owner->userId <> $app->user->id): ?>
                        <?php if(!$relation->renovation_request && ($diff <= 0 && $diff <= $app->config['notifications.seal.toExpire'])):?>
                            <a href="<?php echo $relation->getRequestSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                                <?php \MapasCulturais\i::_e("Solicitar renovação");?>
                            </a>
                        <?php elseif($relation->renovation_request && ($diff <= 0 && $diff <= $app->config['notifications.seal.toExpire'])):?>
                            <div class="alert warning">
                                <?php \MapasCulturais\i::_e("Renovação Solicitada");?>
                            <!--</div>-->
                        <?php endif;?>
                    <?php elseif($entity->owner->userId == $app->user->id && ($diff <= 0 && $diff <= $app->config['notifications.seal.toExpire'])):?>
                        <a href="<?php echo $relation->getRenewSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                            <?php \MapasCulturais\i::_e("Renovar selo");?>
                        </a>
                    <?php endif;?>
                </div>
            <?php endif;?>
            <!--.validade-->

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
