<div class="highlighted-message clearfix" id="opportunity-main-info">
    <?php $this->applyTemplateHook('tab-about--highlighted-message','begin'); ?>

    <?php if($this->isEditable() || $entity->registrationFrom || $entity->registrationTo): ?>
        <?php $this->part('singles/opportunity-about--registration-dates', ['entity' => $entity]) ?>
    <?php endif; ?>

    <?php $this->applyTemplateHook('tab-about--highlighted-message','end'); ?>
</div>