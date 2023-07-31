<?php 
if($entity->isNew()){
    return;
}

$class = $entity->getClassName(); 
?>
<?php $this->applyTemplateHook('entity-status','before'); ?>
<?php if($entity->status === $class::STATUS_DRAFT): ?>
    <div class="alert warning"><?php \MapasCulturais\i::_e("Esta fase da oportunidade é um rascunho.");?></div>
<?php elseif($entity->status === $class::STATUS_TRASH): ?>
    <div class="alert danger"><?php \MapasCulturais\i::_e("Esta fase da oportunidade está na lixeira.");?></div>
<?php endif; ?>

<?php $this->applyTemplateHook('entity-status','after'); ?>