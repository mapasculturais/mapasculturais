<?php if(is_editable() && $entity->canUser('verify')): ?>
<div class="bloco">
    <a
        href="#"
        class="oficial js-verified hltip <?php if(!$entity->isVerified) echo ' inactive'; else echo ' active'; ?>"
        data-verify-url="<?php echo $this->controller->createUrl('verify', array($entity->id)) ?>"
        data-remove-verification-url="<?php echo $this->controller->createUrl('removeVerification', array($entity->id)) ?>" title="Clique para marcar/desmarcar este <?php echo $entity->entityType ?>."
    ></a>
</div>
<?php elseif($entity->isVerified): ?>
<div class="bloco">
    <a class="oficial hltip active" title="Este <?php echo $entity->entityType ?> é verificado." href="#"></a>
</div>
<?php else: ?>
<div class="bloco">
    <a class="oficial hltip" title="Salve para poder verificar este <?php echo $entity->entityType ?>" href="#"></a>
</div>
<?php endif; ?>