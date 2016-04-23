<?php 
if($entity->isNew()){
    return;
}

$class = $entity->getClassName(); 
?>
<?php $this->applyTemplateHook('entity-status','before'); ?>
<?php if($entity->status === $class::STATUS_DRAFT): ?>
    <div class="alert warning">Este <?php echo strtolower($entity->entityType)?> é um rascunho.</div>
<?php elseif($entity->status === $class::STATUS_TRASH): ?>
    <div class="alert danger">Este <?php echo strtolower($entity->entityType)?> está na lixeira.</div>
<?php endif; ?>

<?php $this->applyTemplateHook('entity-status','after'); ?>