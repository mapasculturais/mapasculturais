<?php if(is_editable() && $entity->canUser('verify')): ?>
<div class="bloco hltip" title="Clique para marcar/desmarcar este <?php echo $entity->entityType ?>.">
    <a
        href="#"
        class="oficial js-verified <?php if(!$entity->isVerified) echo ' inactive'; ?>"
        data-verify-url="<?php echo $this->controller->createUrl('verify', array($entity->id)) ?>"
        data-remove-verification-url="<?php echo $this->controller->createUrl('removeVerification', array($entity->id)) ?>"
    ></a>
</div>
<?php elseif($entity->isVerified): ?>
<div class="bloco">
    <a class="oficial hltip" title="Este <?php echo $entity->entityType ?> é verificado." href="#"></a>
</div>
<?php endif; ?>