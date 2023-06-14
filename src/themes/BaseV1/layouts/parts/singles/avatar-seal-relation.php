<?php $this->applyTemplateHook('avatar','before'); ?>

<?php $avatarSize = isset($size) ? $size : 'avatarMedium' ?>
    <?php if($avatar = $entity->avatar): ?>
        <img src="<?php echo $avatar->transform($avatarSize)->url; ?>" alt="" class="js-avatar-img" />
    <?php else: ?>
        <img class="js-avatar-img" src="<?php $this->asset($default_image); ?>" />
    <?php endif; ?>

<!--.avatar-->
<?php $this->applyTemplateHook('avatar','after'); ?>
