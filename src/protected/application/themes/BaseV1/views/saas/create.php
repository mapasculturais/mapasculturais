<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->includeAngularEntityAssets($entity);
?>

<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));  ?>

<header class="saas-header">
	<div class="header-content saas-header-content">
	</div>
</header>
<article class="main-content saas-container">
	<div class="saas-infos">
		<?php if($this->isEditable() || $entity->nome_instalacao): ?>
			<p>
				<span class="label">Nome da instalação:</span>
				<span class="setup-name js-editable" data-edit="certificateText" data-original-title="Nome da Instalação" data-emptytext="Mapa Cultural Santos"><?php echo $entity->nome_instalacao; ?></span>
			</p>
		<?php endif; ?>

	    <?php if($this->isEditable() || $entity->URL): ?>
		    <p>
		    	<span class="label">URL: </span>
		    	<span class="js-editable" data-edit="url" data-original-title="URL" data-emptytext="Ex: .mapas.cultura.gov.br"><?php echo $entity->URL; ?></span>
		    </p>
	    <?php endif; ?>

	    <?php if($this->isEditable() || $entity->entidades_habilitadas): ?>
		    <p>
		        <span class="label">Entidades Habilitadas: </span>
		        <editable-multiselect entity-property="entidades_habilitadas" empty-label="Selecione" allow-other="true" box-title="Entidades habilitadas:"></editable-multiselect>
		    </p>
	    <?php endif; ?>

	    <?php if($this->isEditable() || $entity->cores): ?>
	    	<p>
	    		<span class="label">Cores: </span>
	    		<span class="js-editable inline" data-edit="cor_agentes" data-original-title="Agentes" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_agentes; ?></span>
	    		<span class="js-editable inline" data-edit="cor_espacos" data-original-title="Espaços" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_espacos; ?></span>
	    		<span class="js-editable inline" data-edit="cor_projetos" data-original-title="Projetos" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_projetos; ?></span>
	    		<span class="js-editable inline" data-edit="cor_eventos" data-original-title="Eventos" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_eventos; ?></span>
	    	</p>
	    <?php endif; ?>

	    <?php if($this->isEditable() || $entity->logo_mapas): ?>
		    <p>
		    	<span class="label">Logomarca do Mapas: </span>
		    	<?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
		    </p>
	    <?php endif; ?>
	    <br />
	    <?php if($this->isEditable() || $entity->logo_mapas): ?>
		    <p>
		    	<span class="label">Logomarca da Instituição: </span>
		    	<?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
		    </p>
	    <?php endif; ?>
	    <br />
	    <?php if($this->isEditable() || $entity->imagem_fundo): ?>
		    <p>
		    	<span class="label">Imagem de fundo: </span>
		    	<?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
		    </p>
	    <?php endif; ?>
	</div>