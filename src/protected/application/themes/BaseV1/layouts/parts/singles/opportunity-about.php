<?php
$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
?>

<div id="main-content" class="aba-content">
    <?php $this->applyTemplateHook('tab-about','begin'); ?>

    <?php $this->part('singles/opportunity-about--highlighted-message', ['entity' => $entity]) ?>

    <?php if(!$this->isEditable()): ?>
        <?php $this->part('singles/opportunity-registrations--user-registrations', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--form', ['entity' => $entity]) ?>
    <?php endif; ?>

    <?php $this->part('singles/opportunity-registrations--intro', ['entity' => $entity]); ?>

    <?php $this->part('singles/opportunity-registrations--rules', ['entity' => $entity]); ?>

    <div class="registration-fieldset">
    <!-- Video Gallery BEGIN -->
    <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
    <!-- Video Gallery END -->

    <!-- Image Gallery BEGIN -->
    <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
    <!-- Image Gallery END -->
    </div>

    <?php if ($this->isEditable()): ?>

        <?php $this->part('singles/opportunity-registrations--seals', ['entity' => $entity]) ?>

    <?php endif; ?>

    <?php $this->applyTemplateHook('tab-about','end'); ?>
</div>
<!-- #sobre -->
