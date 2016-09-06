<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$viewModeString = $entityName !== 'project' ? '' : ',viewMode:list';
$tags = $entity->terms['tag'];
?>
<?php if($this->isEditable() || !empty($tags)): ?>
    <div class="widget">
        <span class="label">Filtros:</span>
        <br />
        <?php if($this->isEditable()): ?>
            <span class="js-editable" data-edit="filtro_uf" data-original-title="UF" data-emptytext="UF"><?php echo $entity->filtro_uf; ?></span>
        <?php endif;?>
        <br />
        <?php if($this->isEditable()): ?>
            <span class="js-editable" data-edit="filtro_espaco" data-original-title="Insira um tipo de espaço" data-emptytext="Espaço"><?php echo $entity->filtro_espaco; ?></span>
        <?php endif;?>
        <br />
        <?php if($this->isEditable()): ?>
            <span class="js-editable" data-edit="filtro_area_atuacao" data-original-title="Escolha uma área de atuação" data-emptytext="Área de atuação"><?php echo $entity->filtro_area_atuacao; ?></span>
        <?php endif;?>

    </div>
<?php endif; ?>
