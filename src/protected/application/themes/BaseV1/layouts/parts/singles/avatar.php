<?php $this->applyTemplateHook('avatar','before'); ?>
<div class="avatar <?php if($entity->avatar): ?>com-imagem<?php endif; ?>">
    <?php if($avatar = $entity->avatar): ?>
        <img src="<?php echo $avatar->transform('avatarBig')->url; ?>" alt="" class="js-avatar-img" />
    <?php else: ?>
        <img class="js-avatar-img" src="<?php $this->asset($default_image); ?>" />
    <?php endif; ?>
    <?php if($this->isEditable()): ?>
        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-avatar" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Editar");?></a>
        <div id="editbox-change-avatar" class="js-editbox mc-right" title="<?php \MapasCulturais\i::esc_attr_e("Editar avatar");?>">
            <?php $this->ajaxUploader ($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig', false, [200,200]); ?>
        </div>
    <?php endif; ?>
</div>
<!--.avatar-->
<?php $this->applyTemplateHook('avatar','after'); ?>
