<?php
if($entity->isNew()){
    return;
}

$class = $entity->getClassName();
?>
<?php $this->applyTemplateHook('entity-status','before'); ?>
<?php if($entity->status === $class::STATUS_DRAFT): ?>
    <div class="alert warning"><?php printf(\MapasCulturais\i::__("Este %s é um rascunho"), strtolower($entity->entityTypeLabel()));?></div>
<?php elseif($entity->status === $class::STATUS_TRASH): ?>
    <div class="alert danger"><?php printf(\MapasCulturais\i::__("Este %s está na lixeira"), strtolower($entity->entityTypeLabel()));?></div>
<?php elseif($entity->status === $class::STATUS_ARCHIVED): ?>
    <div class="alert danger"><?php printf(\MapasCulturais\i::__("Este %s está arquivado"), strtolower($entity->entityTypeLabel()));?></div>
<?php endif; ?>

<?php $this->applyTemplateHook('entity-status','after'); ?>
