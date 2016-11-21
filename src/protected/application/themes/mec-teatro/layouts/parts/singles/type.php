<?php $_entity = $this->controller->id; ?>
<?php //Doctrine\Common\Util\Debug::dump(get_class($entity)); ?>
<?php $this->applyTemplateHook('type','before'); ?>
<div class="entity-type <?php echo $_entity ?>-type">
    
    <?php if ($entity instanceof MapasCulturais\Entities\Space): ?>
    
        <span style="display:none" class="js-editable-type" data-original-title="Tipo" data-taxonomy="type" data-entity="<?php echo $_entity ?>" data-value="30"></span>
    
    <?php else : ?>
    
        <div class="icon icon-<?php echo $_entity ?>"></div>
        <a href="#" class='js-editable-type' data-original-title="Tipo" data-emptytext="Seleccione un tipo" data-entity='<?php echo $_entity ?>' data-value='<?php echo $entity->type ?>'>
            <?php echo $entity->type ? $entity->type->name : ''; ?>
        </a>
    
    <?php endif; ?>
    
</div>
<!--.entity-type-->
<?php $this->applyTemplateHook('type','after'); ?>
