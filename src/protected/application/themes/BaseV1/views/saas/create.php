<?php
	$action = preg_replace("#^(\w+/)#", "", $this->template);
	$this->bodyProperties['ng-app'] = "entity.app";
	$this->bodyProperties['ng-controller'] = "EntityController";

	$this->addEntityToJs($entity);

	$this->includeAngularEntityAssets($entity);

	$this->includeMapAssets();

	$this->part('editable-entity', array('entity'=>$entity, 'action'=>$action));
?>
<article class="main-content saas-container">
  <header class="main-content-header">
      <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
	</header>

	<!--.header-image-->
	<div class="header-content">
			<?php $this->applyTemplateHook('header-content','begin'); ?>
			<?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
			<?php $this->applyTemplateHook('header-content','end'); ?>
	</div>
	<!--.header-content-->
	<?php $this->applyTemplateHook('header-content','after'); ?>

	<!--.main-content-header-->
	<?php $this->applyTemplateHook('header','after'); ?>

	<?php $this->applyTemplateHook('tabs','before'); ?>

	<div class="saas-infos">
		<?php if($this->isEditable() || $entity->nome_instalacao): ?>
			<p>
				<span class="label">Nome da instalação:</span>
				<span class="setup-name js-editable" data-edit="name" data-original-title="Nome da Instalação" data-emptytext="Ex.: Mapa Cultural Santos"><?php echo $entity->name; ?></span>
			</p>
		<?php endif; ?>

		<?php if($this->isEditable() || $entity->namespace): ?>
			<p>
				<span class="label">Namespace:</span>
				<span class="setup-name js-editable" data-edit="namespace" data-original-title="Namespace" data-emptytext="Digite um namespace"><?php echo $entity->namespace; ?></span>
			</p>
		<?php endif; ?>

		<?php if($this->isEditable() || $entity->slug): ?>
			<p>
				<span class="label">Slug:</span>
				<span class="setup-name js-editable" data-edit="slug" data-original-title="Slug" data-emptytext="Digite um slug"><?php echo $entity->namespace; ?></span>
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
		        <editable-multiselect entity-property="entidades_habilitadas" empty-label="Selecione" allow-other="false" box-title="Entidades habilitadas:"></editable-multiselect>
		    </p>
	    <?php endif; ?>

	    <?php if($this->isEditable()): ?>
	        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-logo" href="#">Editar</a>
	        <div id="editbox-change-logo" class="js-editbox mc-right" title="Inlcuir logo">
	            <?php $this->ajaxUploader ($entity, 'logo', 'image-src', 'div.logo img.js-logo-img', '', 'logoBig'); ?>
	        </div>
	    <?php endif; ?>

    	<p>
    		<span class="label">Cores: </span>
    		<span class="js-editable inline" data-edit="cor_agentes" data-original-title="Agentes" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_agentes; ?></span>
    		<span class="js-editable inline" data-edit="cor_espacos" data-original-title="Espaços" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_espacos; ?></span>
    		<span class="js-editable inline" data-edit="cor_projetos" data-original-title="Projetos" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_projetos; ?></span>
    		<span class="js-editable inline" data-edit="cor_eventos" data-original-title="Eventos" data-emptytext="Ex.: #FF1212"><?php echo $entity->cor_eventos; ?></span>
    	</p>

	    <?php if($this->isEditable() || $entity->texto_boasvindas): ?>
		    <p>
		        <span class="label">Texto de boas vindas: </span>
		        <span class="js-editable" data-edit="texto_boasvindas" data-original-title="Text de boas vindas" data-emptytext="Escreva um texto de boas vindas de até x caracteres..."><?php echo $entity->texto_boasvindas; ?></span>
		    </p>
	    <?php endif; ?>

	    <?php if($this->isEditable() || $entity->texto_sobre): ?>
		    <p>
		        <span class="label">Texto "sobre": </span>
		        <span class="js-editable" data-edit="texto_sobre" data-original-title="Text sobre" data-emptytext="Escreva um texto e descrição de até x caracteres..."><?php echo $entity->texto_sobre; ?></span>
		    </p>
	    <?php endif; ?>

	    <div class="map">
        	<div id="single-map-container" class="js-map" data-lat="<?php //echo $lat?>" data-lng="<?php //echo $lng?>"></div>
        	<input type="hidden" id="map-target" data-name="location" class="js-editable" data-edit="location" data-value="<?php //echo '[' . $lng . ',' . $lat . ']'; ?>"/>
        	<span class="js-editable" data-edit="lat" data-original-title="Latitude" data-emptytext="Digite a latitude"><?php //echo $entity->$lat; ?></span>
        	<span class="js-editable" data-edit="lng" data-original-title="Longitude" data-emptytext="Digite a longitude"><?php //echo $entity->$lng; ?></span>
    	</div>

	    <p>
	        <span class="label">Filtros: </span>
	        <br />
	        <span class="js-editable" data-edit="filtro1" data-original-title="Filtro 1" data-emptytext="Filtro 1"><?php echo $entity->filtro1; ?></span>
	        <br />
	        <span class="js-editable" data-edit="filtro2" data-original-title="Filtro 2" data-emptytext="Filtro 2"><?php echo $entity->filtro2; ?></span>
	        <br />
	        <span class="js-editable" data-edit="filtro3" data-original-title="Filtro 3" data-emptytext="Filtro 3"><?php echo $entity->filtro3; ?></span>
	        <br />
	        <span class="js-editable" data-edit="filtro4" data-original-title="Filtro 4" data-emptytext="Filtro 4"><?php echo $entity->filtro4; ?></span>

	    </p>
			<p>
				<?php $this->applyTemplateHook('logo','before'); ?>
				<div class="avatar <?php if($entity->avatar): ?>com-imagem<?php endif; ?>">
					<img class="js-avatar-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
	        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-avatar" href="#">Editar</a>
	        <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
	            <?php $this->ajaxUploader($entity, 'logo', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
	        </div>
					<!-- pro responsivo!!! -->
				</div>
				<!--.logo-->
				<?php $this->applyTemplateHook('logo','after'); ?>
			</p>

			<p>
				<?php $this->applyTemplateHook('background','before'); ?>
				<div class="avatar <?php if($entity->avatar): ?>com-imagem<?php endif; ?>">
					<img class="js-avatar-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
	        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-avatar" href="#">Editar</a>
	        <div id="editbox-change-avatar" class="js-editbox mc-right" title="Editar avatar">
	            <?php $this->ajaxUploader($entity, 'logo', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
	        </div>
					<!-- pro responsivo!!! -->
				</div>
				<!--.logo-->
				<?php $this->applyTemplateHook('background','after'); ?>
			</p>
	</div>
