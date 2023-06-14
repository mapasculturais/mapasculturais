<?php if ( $this->isEditable() || $entity->longDescription ): ?>
    <h3><?php \MapasCulturais\i::_e("Descrição");?></h3>
    <span class="descricao js-editable" data-edit="longDescription" data-original-title="<?php $this->dict('entities: Description of the space') ?>" data-emptytext="<?php $this->dict('entities: Description of the space') ?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
<?php endif; ?>

<?php if ( $this->isEditable() || $entity->criterios ): ?>
    <h3><?php $this->dict('entities: Usage criteria of the space') ?></h3>
    <div class="descricao js-editable" data-edit="criterios" data-original-title="<?php $this->dict('entities: Usage criteria of the space') ?>" data-emptytext="<?php $this->dict('entities: Usage criteria of the space') ?>" data-placeholder="<?php $this->dict('entities: Usage criteria of the space') ?>" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->criterios; ?></div>
<?php endif; ?>
