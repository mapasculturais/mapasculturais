<?php if(is_editable() && $entity->canUser('verify')): ?>
<input id="is-verified-input" type="hidden" class="js-editable" data-edit="isVerified" data-value="<?php echo $entity->isVerified ? '1' : '0'?>"/>
<div class="bloco bloco--verified">
    <a href="#"
       class="oficial js-verified hltip <?php if($entity->isVerified) echo ' active'; ?>"
       data-verify-url="<?php echo $this->controller->createUrl('verify', array($entity->id)) ?>"
       data-remove-verification-url="<?php echo $this->controller->createUrl('removeVerification', array($entity->id)) ?>" 
       title="Clique para marcar/desmarcar este <?php echo $entity->entityType ?>."
    ></a>
</div>
<?php elseif($entity->isVerified): ?>
<div class="bloco">
    <a class="oficial hltip active" title="Este <?php echo $entity->entityType ?> é verificado." href="#"></a>
</div>
<?php endif; ?>