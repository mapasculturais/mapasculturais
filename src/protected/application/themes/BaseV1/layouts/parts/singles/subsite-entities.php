<?php $editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';?>
<div id="entidades" class="aba-content">
    <?php if($this->isEditable() || $entity->entidades_habilitadas): ?>
        <p>
            <span class="label">Entidades Habilitadas: </span>
            <editable-multiselect entity-property="entidades_habilitadas" empty-label="Selecione" allow-other="false" box-title="Entidades habilitadas:"></editable-multiselect>
        </p>
    <?php endif; ?>

    <p>
        <span class="label">Cores: </span>
        <span class="js-editable inline <?php echo ($entity->isPropertyRequired($entity,"cor_agentes") && $editEntity? 'required': '');?>" data-edit="cor_agentes" data-original-title="Agentes" data-emptytext="Agentes"><?php echo $entity->cor_agentes; ?></span>
        <span class="js-editable inline <?php echo ($entity->isPropertyRequired($entity,"cor_espacos") && $editEntity? 'required': '');?>" data-edit="cor_espacos" data-original-title="Espaços" data-emptytext="Espaços"><?php echo $entity->cor_espacos; ?></span>
        <span class="js-editable inline <?php echo ($entity->isPropertyRequired($entity,"cor_projetos") && $editEntity? 'required': '');?>" data-edit="cor_projetos" data-original-title="Projetos" data-emptytext="Projetos"><?php echo $entity->cor_projetos; ?></span>
        <span class="js-editable inline <?php echo ($entity->isPropertyRequired($entity,"cor_eventos") && $editEntity? 'required': '');?>" data-edit="cor_eventos" data-original-title="Eventos" data-emptytext="Eventos"><?php echo $entity->cor_eventos; ?></span>
        <span class="js-editable inline <?php echo ($entity->isPropertyRequired($entity,"cor_selo") && $editEntity? 'required': '');?>" data-edit="cor_selos" data-original-title="Selos" data-emptytext="Selos"><?php echo $entity->cor_selos; ?></span>
    </p>
</div>
