<?php 
if($entity->isNew()){
    return;
}

$class = $entity->getClassName(); 
?>
<?php $this->applyTemplateHook('entity-status','before'); ?>
<?php if($entity->status === $class::STATUS_DRAFT): ?>
    <div class="alert warning">Esta fase do projeto é um rascunho.</div>
<?php elseif($entity->status === $class::STATUS_TRASH): ?>
    <div class="alert danger">Esta fase do projeto está na lixeira.</div>
<?php endif; ?>

<?php $this->applyTemplateHook('entity-status','after'); ?>