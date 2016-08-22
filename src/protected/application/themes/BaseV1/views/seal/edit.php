<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('area');

    $this->addTaxonoyTermsToJs('tag');
}

$this->includeMapAssets();

$this->includeAngularEntityAssets($entity);

?>
<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content seal">
    <header class="main-content-header">
        <?php $this->part('singles/header-image', ['entity' => $entity]); ?>

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
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                     <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

					<p>
						<span class="label">Validade:</span>
						<span class="js-editable" data-edit="validPeriod" data-original-title="Periodo" data-emptytext="Informe o período de duração da validade do selo"><?php echo $entity->validPeriod;?></span>
						Meses.<p class="registration-help">(Informar 0 (zero) para validade infinita.)</p>
					</p>

                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>

            </div>
            <!--.ficha-spcultura-->

            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3>Descrição</h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição do Agente" data-emptytext="Insira uma descrição do agente" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>
            <!--.descricao-->

            <?php if ( $this->isEditable() || $entity->certificateText ): ?>
                <h3>Conteúdo da Impressão</h3>
                <p class="registration-help">Para personalizar o conteúdo da impressão do selo aplicado, é possível utilizar as seguintes palavras chaves para obter informações das entidades relacionadas com o selo no texto abaixo.<br>
                [sealName]: Nome do selo aplicado<br>
                [sealShortDescription]: Descrição curta do selo aplicado</br>
                [sealOwner]: Agente que aplicou o selo</br>
                [sealRelationLink]: Link de autencidade do selo aplicado</br>
                [entityDefinition]: Descrição da entidade (Agente/Projeto/Espaço/Evento)<br>
                [entityName]: Nome da entidade (Teatro Municipal)<br>
                [dateIni]: Data de Início da Validade do selo aplicado<br>
                [dateFin]: Data de Fim da Validade do selo aplicado</p>
                <span class="descricao js-editable" data-edit="certificateText" data-original-title="Conteúdo da Impressão do Certificado" data-emptytext="Insira o conteúdo da impressão do certificado do selo." ><?php echo $this->isEditable() ? $entity->certificateText: nl2br($entity->certificateText); ?></span>
            <?php endif; ?>
            <!--.conteúdo da impressão do certificado do selo-->
        </div>
        <!-- #sobre -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)); ?>
</article>
<div class="sidebar-left sidebar seal">

</div>
<div class="sidebar seal sidebar-right">

	<!-- Related Agents BEGIN -->
        <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->

	<!-- Downloads BEGIN -->
        <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>
