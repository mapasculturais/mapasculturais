<?php if ( $this->isEditable() || $entity->longDescription ): ?>
    <h3><?php \MapasCulturais\i::_e("Descrição");?></h3>
    <span class="descricao js-editable" data-edit="longDescription" data-original-title="Descrição <?php $this->dict('entities: of the Space') ?>" data-emptytext="Insira uma descrição <?php $this->dict('entities: of the space') ?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
<?php endif; ?>

<?php if ( $this->isEditable() || $entity->criterios ): ?>
    <h3><?php \MapasCulturais\i::_e("Critérios de uso");?> <?php $this->dict('entities: of the space') ?></h3>
    <div class="descricao js-editable" data-edit="criterios" data-original-title="Critérios de uso <?php $this->dict('entities: of the space') ?>" data-emptytext="Insira os critérios de uso <?php $this->dict('entities: of the space') ?>" data-placeholder="Insira os critérios de uso <?php $this->dict('entities: of the space') ?>" data-showButtons="bottom" data-placement="bottom"><?php echo $entity->criterios; ?></div>
<?php endif; ?>