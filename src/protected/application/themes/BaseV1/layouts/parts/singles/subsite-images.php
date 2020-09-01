<div id="imagens" class="aba-content">
    <p class="alert info">
        <?php \MapasCulturais\i::_e('Nesta seção você configura as imagens que vão aparecer na instalação. É possível selecionar o logo da instalação, o background e o logo da instituição.'); ?>
    </p>
    <div style="margin-bottom:2em">
        <h3 class="label">
            <?php \MapasCulturais\i::_e('Logo:'); ?>
        </h3>

        <?php $this->applyTemplateHook('logo','before'); ?>

        <div class="logo <?php if($entity->logo): ?>com-imagem<?php endif; ?>">
            <?php if($entity->logo): ?>
                <img class="js-logo-img" src="<?php echo $entity->logo->transform('logo')->url; ?>" />
            <?php else: ?>
                <img class="js-logo-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
            <?php endif; ?>

            <?php if($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-logo" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Editar'); ?></a>
                <div id="editbox-change-logo" class="js-editbox mc-right" title="Editar logo">
                    <?php $this->ajaxUploader($entity, 'logo', 'image-src', 'div.logo img.js-logo-img', '', 'logo'); ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="tip"><?php printf(\MapasCulturais\i::__('Deve ter as dimensões de %s com extensões %s'), '<strong>140x60px</strong>', '<strong>.png/.jpg</strong>'); ?></span>

        <?php $this->applyTemplateHook('logo','after'); ?>
    </div>

    <div style="margin-bottom:2em">
        <h3 class="label">
            <?php \MapasCulturais\i::_e('Background:'); ?>
        </h3>

        <?php $this->applyTemplateHook('background','before'); ?>

        <div class="background <?php if($entity->background): ?>com-imagem<?php endif; ?>">
            <?php if($entity->background): ?>
                <img class="js-background-img" src="<?php echo $entity->background->transform('background')->url; ?>" />
            <?php else: ?>
                    <img class="js-background-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
            <?php endif; ?>

            <?php if($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-background" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Editar'); ?></a>
                <div id="editbox-change-background" class="js-editbox mc-right" title="Editar Imagem de Fundo">
                    <?php $this->ajaxUploader($entity, 'background', 'image-src', 'div.background img.js-background-img', '', 'background'); ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="tip">
            <?php printf(\MapasCulturais\i::__('Deve ter extensões %s com extensões %s e deve ter fundo transparente'), '<strong>680x680px</strong>', '<strong>.png/.jpg</strong>'); ?>
        </span>

        <?php $this->applyTemplateHook('background','after'); ?>
    </div>

    <div style="margin-bottom:2em">
        <h3 class="label">
            <?php \MapasCulturais\i::_e('Logo da Instituição:'); ?>
        </h3>

        <?php $this->applyTemplateHook('institute','before'); ?>

        <div class="institute <?php if($entity->institute): ?>com-imagem<?php endif; ?>">
            <?php if($entity->institute): ?>
                <img class="js-institute-img" src="<?php echo $entity->institute->transform('institute')->url; ?>" />
            <?php else: ?>
                <img class="js-institute-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
            <?php endif; ?>

            <?php if($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-institute" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Editar'); ?></a>
                <div id="editbox-change-institute" class="js-editbox mc-right" title="Editar Logo Instituição">
                    <?php $this->ajaxUploader($entity, 'institute', 'image-src', 'div.institute img.js-institute-img', '', 'institute'); ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="tip">
            <?php printf(\MapasCulturais\i::__('Deve ter as dimensões de %s com extensões %s'), '<strong>90x39px</strong>', '<strong>.png/.jpg</strong>'); ?>
        </span>

        <?php $this->applyTemplateHook('institute','after'); ?>
    </div>


    <div style="margin-bottom:2em">
        <h3 class="label">
            <?php \MapasCulturais\i::_e('Imagem de compartilhamento padrão:'); ?>
        </h3>

        <?php $this->applyTemplateHook('share','before'); ?>

        <div class="share <?php if($entity->share): ?>com-imagem<?php endif; ?>">
            <?php if($entity->share): ?>
                <img class="js-share-img" src="<?php echo $entity->share->transform('share')->url; ?>" />
            <?php else: ?>
                <img class="js-share-img" src="<?php $this->asset('img/avatar--space.png'); ?>" />
            <?php endif; ?>

            <?php if($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-share" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Editar'); ?></a>
                <div id="editbox-change-share" class="js-editbox mc-right" title="Editar Logo Instituição">
                    <?php $this->ajaxUploader($entity, 'share', 'image-src', 'div.share img.js-share-img', '', 'share'); ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="tip">
            <?php printf(\MapasCulturais\i::__('Deve ter as dimensões de %s com extensões %s'), '<strong>1200 x 630px</strong>', '<strong>.png/.jpg</strong>'); ?>
        </span>

        <?php $this->applyTemplateHook('share','after'); ?>
    </div>

    <div style="margin-bottom:2em">
        <h3 class="label">
            <?php \MapasCulturais\i::_e('Favicon da Instalação:'); ?>
        </h3>

        <?php $this->applyTemplateHook('favicon','before'); ?>

        <div class="favicon <?php if($entity->favicon): ?>com-imagem<?php endif; ?>">
            <?php if($entity->favicon): ?>
                <img class="js-favicon-img" src="<?php echo $entity->favicon->url; ?>" />
            <?php else: ?>
                <img class="js-favicon-img" src="<?php $this->asset('img/favicon.ico'); ?>" />
            <?php endif; ?>

            <?php if($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-favicon" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e('Editar'); ?></a>
                <div id="editbox-change-favicon" class="js-editbox mc-right" title="Editar Favicon da Instalação">
                    <?php $this->ajaxUploader($entity, 'favicon', 'image-src', 'div.favicon img.js-favicon-img', '', 'favicon',false,false,".ico/.icon/.jpg/.png"); ?>
                </div>
            <?php endif; ?>
        </div>

        <span class="tip">
            <?php printf(\MapasCulturais\i::__('Deve ter as dimensões de %s com extensões %s'), '<strong>16x16px</strong>', '<strong>.ico/.icon/.png/.jpg</strong>'); ?>
        </span>

        <?php $this->applyTemplateHook('favicon','after'); ?>
    </div>
</div>
