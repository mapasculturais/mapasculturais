<div id="imagens" class="aba-content">
    <div class="logo">
        <h3 class="label">
            Logo: <span class="tip">Deve ter as dimensões de 140x60px com extensões <strong>.png/.jpg</strong></span>
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

        <?php $this->applyTemplateHook('logo','after'); ?>
    </div>

    <div class="background">
        <h3 class="label">
            Background: <span class="tip">Deve ter as dimensões de 1200x630px com extensões .png/.jpg)</span>
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

        <?php $this->applyTemplateHook('background','after'); ?>
    </div>
</div>
