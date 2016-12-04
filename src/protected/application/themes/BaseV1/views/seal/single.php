<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
}

$this->includeMapAssets();

$this->includeAngularEntityAssets($entity);

?>
<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'seals','home_title' => 'entities: My Seals']); ?>

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<article class="main-content label">
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
        <li class="active"><a href="#sobre"><?php \MapasCulturais\i::_e("Sobre");?></a></li>
        <?php if(!($this->controller->action === 'create')):?>
        <li><a href="#permissao"><?php \MapasCulturais\i::_e("Permissões");?></a></li>
        <?php endif;?>
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
                <?php $this->applyTemplateHook('tab-about-service','before'); ?>
                <div class="servico">
                    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

					<p>
						<span class="label"><?php \MapasCulturais\i::_e("Validade");?>:</span>
						<span class="js-editable" data-edit="validPeriod" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Periodo");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe o período de duração da validade do selo");?>">
            <?php
              if($this->isEditable() || $entity->validPeriod > 0){
                echo $entity->validPeriod;
              } else {
                echo \MapasCulturais\i::_e("Selo sem prazo de Validade");
              }
            ?>

            </span><?php if($entity->validPeriod > 0) echo $entity->validPeriod > 1 ? 'Meses':'Mês' ?>

            <?php if ($this->isEditable()): ?>
              <p class="registration-help"><?php \MapasCulturais\i::_e("(Informar 0 (zero) para validade infinita ou indicar o número de meses correspondente a validade do selo)");?></p>
            <?php endif; ?>
					</p>

                    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
                </div>
                <?php $this->applyTemplateHook('tab-about-service','after'); ?>
            </div>
            <!--.ficha-spcultura-->

      <?php if($this->isEditable() || $entity->longDescription) {?>
  			<p>
  				<h3><?php \MapasCulturais\i::_e("Descrição");?></h3>
  				<span class="descricao js-editable" data-edit="longDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição do Selo");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição do selo");?>" ><?php echo $this->isEditable ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
  				<!--.descricao-->
  			</p>
      <?php } ?>

      <?php if($this->isEditable()) {?>
  			<p>
  				<h3><?php \MapasCulturais\i::_e("Conteúdo da Impressão");?></h3>
            <p class="registration-help"><?php \MapasCulturais\i::_e("Para personalizar o conteúdo da impressão do selo aplicado, é possível utilizar as seguintes palavras chaves para obter informações das entidades relacionadas com o selo no texto abaixo.");?><br>
            [sealName]: <?php \MapasCulturais\i::_e("Nome do selo aplicado");?><br>
            [sealShortDescription]: <?php \MapasCulturais\i::_e("Descrição curta do selo aplicado");?></br>
            [sealOwner]: <?php \MapasCulturais\i::_e("Agente que aplicou o selo");?></br>
            [sealRelationLink]: <?php \MapasCulturais\i::_e("Link de autencidade do selo aplicado");?></br>
            [entityDefinition]: <?php \MapasCulturais\i::_e("Descrição da entidade (Agente/Projeto/Espaço/Evento)");?><br>
            [entityName]: <?php \MapasCulturais\i::_e("Nome da entidade (Teatro Municipal)");?><br>
            [dateIni]: <?php \MapasCulturais\i::_e("Data de Início da Validade do selo aplicado");?><br>
            [dateFin]: <?php \MapasCulturais\i::_e("Data de Fim da Validade do selo aplicado");?></p>
  				<span class="descricao js-editable" data-edit="certificateText" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Conteúdo da Impressão do Certificado");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira o conteúdo da impressão do certificado do selo.");?>" ><?php echo nl2br($entity->certificateText); ?></span>
  	      <!--.conteúdo da impressão do certificado do selo-->
  			</p>
      <?php }?>
      </div>
      <!-- #sobre -->

      <!-- #permissao -->
      <?php $this->part('singles/permissions') ?>
      <!-- #permissao -->
      <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>

    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)); ?>
</article>
<div class="sidebar-left sidebar seal">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para imagens, download ou links, primeiro é preciso salvar o selo");?>.<span class="close"></span></p>
        </div>
    <?php endif; ?>
</div>
<div class="sidebar seal sidebar-right">

    <!-- Related Admin Agents BEGIN -->
        <?php $this->part('related-admin-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Admin Agents END -->

	<!-- Related Agents BEGIN -->
        <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->

    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para imagens, download ou links, primeiro é preciso salvar o selo");?>.<span class="close"></span></p>
        </div>
    <?php endif; ?>

	<!-- Downloads BEGIN -->
        <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->
</div>
