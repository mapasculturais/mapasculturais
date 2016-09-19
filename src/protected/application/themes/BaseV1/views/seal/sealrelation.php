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
				<?php if($app->isEnabled('seals') && ($app->user->is('superAdmin') || $app->user->is('admin'))) {?>
					<a class="btn btn-default js-open-editbox" href="<?php echo $app->createUrl('seal','printsealrelation',[$relation->id]);?>">Imprimir Certificado</a>
				<?php } ?>
			</div>
		<?php $this->applyTemplateHook('header-image','after'); ?>

        <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>

        <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>

            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--seal.png']); ?>

            <?php $this->part('singles/name', ['entity' => $entity]) ?>

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
        <li class="active"><a href="#sobre">Sobre</a></li>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <div class="ficha-spcultura">
                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
            </div>
            <!--.ficha-spcultura-->

            <?php if ( $entity->longDescription ): ?>
                <h3>Descrição</h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Selo" data-emptytext="Insira uma descrição do selo" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>
            <!--.descricao-->
						<?php
						/*
						 * Mapas Culturais entity seal atributed printing.
						 */
						//$entity = $relation->seal;
						//$app = App::i();
						$period = new DateInterval("P" . $entity->validPeriod . "M");
						$dateIni = $relation->createTimestamp->format("d/m/Y");
						$dateFin = $relation->createTimestamp->add($period);
						$dateFin = $dateFin->format("d/m/Y");

						$mensagem = $relation->seal->certificateText;
						$mensagem = str_replace("\t","&nbsp;&nbsp;&nbsp;&nbsp",$mensagem);
						$mensagem = str_replace("[sealName]",$relation->seal->name,$mensagem);
						$mensagem = str_replace("[sealOwner]",$relation->seal->agent->name,$mensagem);
						$mensagem = str_replace("[sealShortDescription]",$relation->seal->shortDescription,$mensagem);
						$mensagem = str_replace("[sealRelationLink]",$app->createUrl('seal','printsealrelation',[$relation->id]),$mensagem);
						$mensagem = str_replace("[entityDefinition]",$relation->owner->entityType,$mensagem);
						$mensagem = str_replace("[entityName]",$relation->owner->name,$mensagem);
						$mensagem = str_replace("[dateIni]",$dateIni,$mensagem);
						$mensagem = str_replace("[dateFin]",$dateFin,$mensagem);
						?>
						<p>
							<h3>Conteúdo da Impressão</h3>
							<span class="descricao js-editable" data-edit="certificateText" data-original-title="Conteúdo da Impressão do Certificado" data-emptytext="Insira o conteúdo da impressão do certificado do selo." ><?php echo $mensagem; ?></span>
				            <!--.conteúdo da impressão do certificado do selo-->
						</p>
        </div>
        <!-- #sobre -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

    <?php /*$this->part('owner', array('entity' => $entity, 'owner' => $entity->owner));*/ ?>
</article>
<div class="sidebar-left sidebar seal">

</div>
<div class="sidebar seal sidebar-right">

</div>
