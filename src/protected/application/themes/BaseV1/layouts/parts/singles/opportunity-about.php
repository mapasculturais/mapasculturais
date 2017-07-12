<?php
$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
?>

<div id="main-content" class="aba-content">
    <?php $this->applyTemplateHook('tab-about','begin'); ?>

    <?php $this->part('singles/opportunity-about--highlighted-message', ['entity' => $entity]) ?>

    <?php $this->part('singles/opportunity-registrations--intro', ['entity' => $entity]); ?>

    <!-- Video Gallery BEGIN -->
    <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
    <!-- Video Gallery END -->

    <!-- Image Gallery BEGIN -->
    <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
    <!-- Image Gallery END -->

    <?php $this->part('singles/opportunity-registrations--rules', ['entity' => $entity]); ?>
    
    <?php if(!$this->isEditable()): ?>
        <?php $this->part('singles/opportunity-registrations--user-registrations', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--form', ['entity' => $entity]) ?>
    <?php endif; ?>
    
    <?php if ($this->isEditable()): ?>

        <?php $this->part('singles/opportunity-registrations--categories', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--agent-relations', ['entity' => $entity]) ?>
        
        <?php $this->part('singles/opportunity-registrations--seals', ['entity' => $entity]) ?>

        <?php $this->part('singles/opportunity-registrations--fields', ['entity' => $entity]) ?>
        
        <?php $this->part('singles/opportunity-registrations--importexport', ['entity' => $entity]) ?>
        
    <?php endif; ?>

    <?php $this->applyTemplateHook('tab-about','end'); ?>
</div>
<!-- #sobre -->
