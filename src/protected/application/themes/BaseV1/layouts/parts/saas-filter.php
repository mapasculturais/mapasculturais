<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$viewModeString = $entityName !== 'project' ? '' : ',viewMode:list';
$tags = $entity->terms['tag'];
?>
<?php if($this->isEditable() || !empty($tags)): ?>
    <div class="widget">
        <span class="label">Filtros:</span>
        <?php if($this->isEditable()): ?>
            <span class="js-editable" data-edit="filtro_uf" data-original-title="UF" data-emptytext="UF"><?php echo $entity->filtro_uf; ?></span>
        <?php endif;?>
    </div>
<?php endif; ?>
