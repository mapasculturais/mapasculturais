<div id="imagens" class="aba-content">
    <p class="alert info">Nesta seção você configura as imagens que vão aparecer na instalação. É possível selecionar o logo da instalação, o background e o logo da instituição.</p>
    <div class="logo-container">
        <h3 class="label">
            Logo:
        </h3>

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
        </div>

        <span class="tip">Deve ter as dimensões de <strong>140x60px</strong> com extensões <strong>.png/.jpg</strong></span>

        <?php $this->applyTemplateHook('logo','after'); ?>
    </div>

    <div class="background-container">
        <h3 class="label">
            Background:
        </h3>

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
        </div>

        <span class="tip">Deve ter extensões <strong>680x680px</strong> com extensões <strong>.png/.jpg</strong> e deve ter fundo transparente</span>

        <?php $this->applyTemplateHook('background','after'); ?>
    </div>

    <div class="institute-container">
        <h3 class="label">
            Logo da Instituição:
        </h3>

        <?php $this->applyTemplateHook('institute','before'); ?>

        <div class="institute <?php if($entity->institute): ?>com-imagem<?php endif; ?>">
            <?php if($entity->institute): ?>
                <img class="js-institute-img" src="<?php echo $entity->institute->transform('institute')->url; ?>" />
            <?php else: ?>
                <img class="js-institute-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
            <?php endif; ?>

            <?php if($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-institute" href="#">Editar</a>
                <div id="editbox-change-institute" class="js-editbox mc-right" title="Editar Logo Instituição">
                    <?php $this->ajaxUploader($entity, 'institute', 'image-src', 'div.institute img.js-institute-img', '', 'institute'); ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="tip">Deve ter as dimensões de <strong>90x39px</strong> com extensões <strong>.png/.jpg</strong></span>

        <?php $this->applyTemplateHook('institute','after'); ?>
    </div>
</div>
