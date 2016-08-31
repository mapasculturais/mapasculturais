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
	<br>

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

	    <?php if($this->isEditable() || $entity->url): ?>
		    <p>
		    	<span class="label">URL: </span>
		    	<span class="js-editable" data-edit="url" data-original-title="URL" data-emptytext="Ex: .mapas.cultura.gov.br"><?php echo $entity->url; ?></span>
		    </p>
	    <?php endif; ?>

	    <?php if($this->isEditable() || $entity->entidades_habilitadas): ?>
		    <p>
		        <span class="label">Entidades Habilitadas: </span>
		        <editable-multiselect entity-property="entidades_habilitadas" empty-label="Selecione" allow-other="false" box-title="Entidades habilitadas:"></editable-multiselect>
		    </p>
	    <?php endif; ?>

    	<p>
    		<span class="label">Cores: </span>
    		<span class="js-editable inline" data-edit="cor_agentes" data-original-title="Agentes" data-emptytext="Agentes"><?php echo $entity->cor_agentes; ?></span>
    		<span class="js-editable inline" data-edit="cor_espacos" data-original-title="Espaços" data-emptytext="Espaços"><?php echo $entity->cor_espacos; ?></span>
    		<span class="js-editable inline" data-edit="cor_projetos" data-original-title="Projetos" data-emptytext="Projetos"><?php echo $entity->cor_projetos; ?></span>
    		<span class="js-editable inline" data-edit="cor_eventos" data-original-title="Eventos" data-emptytext="Eventos"><?php echo $entity->cor_eventos; ?></span>
				<span class="js-editable inline" data-edit="cor_selos" data-original-title="Selos" data-emptytext="Selos"><?php echo $entity->cor_selos; ?></span>
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

	    <?php if($this->isEditable() || $entity->latitude): ?>
		    <p>
		        <span class="label">Latitude: </span>
		        <span class="js-editable" data-edit="latitude" data-original-title="Latitude" data-emptytext="Ex.: 40.7143528"><?php echo $entity->latitude; ?></span>		    </p>
	    <?php endif; ?>

	    <?php if($this->isEditable() || $entity->longitude): ?>
		    <p>
		        <span class="label">Longitude: </span>
		        <span class="js-editable" data-edit="longitude" data-original-title="longitude" data-emptytext="Ex.: 41 24.2028"><?php echo $entity->longitude; ?></span>		    </p>
	    <?php endif; ?>
			<p class="tip">Para saber como obter coordenadas de latitude e longitude, visite: <a href="https://support.google.com/maps/answer/18539?hl=pt-BR" title="Página de suporte do Google Maps" target="_blank">Ajuda Google Maps.</a></p>
			<?php if($this->isEditable() || $entity->zoom_default): ?>
		    <p>
					<span class="label">Zoom Padrão: </span>
					<span class="js-editable" data-edit="zoom_default" data-original-title="Zoom Padrão" data-emptytext="Zoom padrão do mapa"><?php echo $entity->zoom_default;?></span>
				</p>
			<?php endif;?>

			<?php if($this->isEditable() || $entity->zoom_approximate): ?>
		    <p>
					<span class="label">Zoom Aproximado: </span>
					<span class="js-editable" data-edit="zoom_approximate" data-original-title="Zoom Aproximado" data-emptytext="Zoom aproximado do mapa"><?php echo $entity->zoom_approximate;?></span>
				</p>
			<?php endif;?>

			<?php if($this->isEditable() || $entity->zoom_precise): ?>
		    <p>
					<span class="label">Zoom Preciso: </span>
					<span class="js-editable" data-edit="zoom_precise" data-original-title="Zoom Preciso" data-emptytext="Zoom preciso do mapa"><?php echo $entity->zoom_precise;?></span>
				</p>
			<?php endif;?>

			<?php if($this->isEditable() || $entity->zoom_min): ?>
		    <p>
					<span class="label">Zoom Mínimo: </span>
					<span class="js-editable" data-edit="zoom_min" data-original-title="Zoom Mínimo" data-emptytext="Zoom mínimo do mapa"><?php echo $entity->zoom_min;?></span>
				</p>
			<?php endif;?>

			<?php if($this->isEditable() || $entity->zoom_max): ?>
		    <p>
					<span class="label">Zoom Máximo: </span>
					<span class="js-editable" data-edit="zoom_max" data-original-title="Zoom Máximo" data-emptytext="Zoom máximo do mapa"><?php echo $entity->zoom_max;?></span>
				</p>
			<?php endif;?>

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
				<span class="label">Logo: (Deve ter as dimensões de 140x60px com extensões .png/.jpg) </span>
				<?php $this->applyTemplateHook('logo','before'); ?>
				<div class="logo <?php if($entity->logo): ?>com-imagem<?php endif; ?>">
					<?php if($entity->logo): ?>
					    <img class="js-logo-img" src="<?php echo $entity->logo->transform('logo')->url; ?>" />
					<?php else: ?>
							<img class="js-logo-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
					<?php endif; ?>
					<?php if($this->isEditable()): ?>
		        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-logo" href="#">Editar</a>
		        <div id="editbox-change-logo" class="js-editbox mc-right" title="Editar logo">
		            <?php $this->ajaxUploader($entity, 'logo', 'image-src', 'div.logo img.js-logo-img', '', 'logo'); ?>
		        </div>
					<?php endif; ?>
					<!-- pro responsivo!!! -->
				</div>
				<!--.logo-->
				<?php $this->applyTemplateHook('logo','after'); ?>
			</p>
			<br>
			<br>
			<br>
			<br>
			<p>
				<span class="label">Background: (Deve ter as dimensões de 1200x630px com extensões .png/.jpg) </span>
				<?php $this->applyTemplateHook('background','before'); ?>
				<div class="background <?php if($entity->background): ?>com-imagem<?php endif; ?>">
					<?php if($entity->background): ?>
					    <img class="js-background-img" src="<?php echo $entity->background->transform('background')->url; ?>" />
					<?php else: ?>
							<img class="js-background-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
					<?php endif; ?>
					<?php if($this->isEditable()): ?>
		        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-background" href="#">Editar</a>
		        <div id="editbox-change-background" class="js-editbox mc-right" title="Editar Imagem de Fundo">
		            <?php $this->ajaxUploader($entity, 'background', 'image-src', 'div.background img.js-background-img', '', 'background'); ?>
		        </div>
					<?php endif; ?>
					<!-- pro responsivo!!! -->
				</div>
				<!--.logo-->
				<?php $this->applyTemplateHook('background','after'); ?>
			</p>
	</div>
