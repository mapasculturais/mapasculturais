    <!--.header-image-->
    <div class="header-content">
            <?php $this->applyTemplateHook('header-content','begin'); ?>
            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--space.png']); ?>
            <?php $this->applyTemplateHook('header-content','end'); ?>

            <?php if($this->isEditable() || $entity->nome_instalacao): ?>
                <p>
                    <span class="setup-name js-editable required" data-edit="name" data-original-title="Nome da Instalação" data-emptytext="Nome da instalação"><?php echo $entity->name; ?></span>
                </p>
            <?php endif; ?>

            <div>
                <span class="icon"></span><span class="label">Tema:</span>
                <span class="js-editable required" data-edit="namespace" data-original-title="Tema" data-emptytext="Selecione a o tema a ser utilizado"><?php echo $entity->namespace; ?></span>
            </div>

            <?php $this->part('singles/subsite-header--domains', ['entity' => $entity]) ?>

    </div>
    <!--.header-content-->
    <?php $this->applyTemplateHook('header-content','after'); ?>

    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>