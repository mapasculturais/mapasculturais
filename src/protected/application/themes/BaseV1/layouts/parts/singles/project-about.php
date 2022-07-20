<?php
$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
?>

<div id="sobre" class="aba-content">
    <?php $this->applyTemplateHook('tab-about','begin'); ?>

    <?php $this->part('singles/project-about--highlighted-message', ['entity' => $entity]) ?>

    
    <?php if ( $this->isEditable() || $entity->longDescription ): ?>
        <h3 class="<?php echo ($entity->isPropertyRequired($entity,"site") && $editEntity? 'required': '');?>"><?php \MapasCulturais\i::_e("Descrição");?></h3>
        <span class="descricao js-editable" data-edit="longDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição do Projeto");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição do projeto");?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
    <?php endif; ?>


    <!-- Video Gallery BEGIN -->
    <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?>
    <!-- Video Gallery END -->

    <!-- Image Gallery BEGIN -->
    <?php $this->part('gallery.php', array('entity'=>$entity)); ?>
    <!-- Image Gallery END -->

    <?php $this->applyTemplateHook('tab-about','end'); ?>
</div>
<!-- #sobre -->
