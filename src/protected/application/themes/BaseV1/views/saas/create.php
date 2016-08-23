<header class="saas-header">
	<div class="header-content saas-header-content">
	</div>
</header>
<article class="main-content saas-container">
	<div class="saas-infos">
		<span class="label">Nome da instalação:</span>
		<span class="setup-name js-editable" data-edit="certificateText" data-original-title="Nome da Instalação" data-emptytext="Mapa Cultural Santos">Mapa Cultural Santos</span>

		<span class="label">URL:</span>
		<span class="url js-editable" data-edit="certificateText" data-original-title="URL da instalação" data-emptytext="Mapa Cultural Santos">.mapas.cultura.gov.br</span>

		<span class="label">Entidades habilitadas:</span>

        <div id="boas-vindas" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>
            <div class="upload">
            	<span class="label">Logomarca do Mapas:</span>
                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="Descrição Curta" data-emptytext="Insira uma descrição curta" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
                <?php $this->part('singles/saas-setup'); ?>

                <?php $this->part('singles/location', ['has_private_location' => false]); ?>
            </div>

            <?php $this->part('singles/space-extra-info') ?>

        </div>

	</div>
</article>