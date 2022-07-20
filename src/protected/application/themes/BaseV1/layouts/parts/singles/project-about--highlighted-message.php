<?php $this->applyTemplateHook('tab-about--highlighted-message','before'); ?>
<div class="highlighted-message clearfix">
    <?php $this->applyTemplateHook('tab-about--highlighted-message','begin'); ?>

    <?php if($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
        <?php $this->part('singles/project-about--registration-dates', ['entity' => $entity]) ?>
    <?php endif; ?>

    <?php $this->applyTemplateHook('tab-about--highlighted-message','end'); ?>
</div>
<?php $this->applyTemplateHook('tab-about--highlighted-message','after'); ?>