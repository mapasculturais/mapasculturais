<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$viewModeString = $entityName !== 'project' ? '' : ',viewMode:list';
$tags = $entity->terms['tag'];
$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';
?>
<?php if($this->isEditable() || !empty($tags)): ?>
    <div id="filtros" class="aba-content">
        <span class="label">Filtros:</span>
        <?php if($this->isEditable() || $entity->filtro_agent_meta_En_Estado): ?>
            <p>
              <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Estado") && $editEntity? 'required': '');?>">Estado: </span>
              <span class="js-editable" data-edit="filtro_agent_meta_En_Estado" data-original-title="Estado" data-emptytext="Selecione o(s) estado(s) para o(s) Agente(s)"></span>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_space_meta_En_Estado): ?>
            <p>
              <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Estado") && $editEntity? 'required': '');?>">Estado: </span>
              <span class="js-editable" data-edit="filtro_space_meta_En_Estado" data-original-title="Estado" data-emptytext="Selecione o(s) estado(s) para o(s) Espaço(s)"></span>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_agent_term_area): ?>
            <p>
              <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_term_area") && $editEntity? 'required': '');?>">Área de Atuação do Agente: </span>
              <span class="js-editable" data-edit="filtro_agent_term_area" data-original-title="Área de Atuação" data-emptytext="Selecione a(s) área(s) de atuação"></span>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_space_term_area): ?>
            <p>
              <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_term_area") && $editEntity? 'required': '');?>">Área de Atuação do Espaço: </span>
              <span class="js-editable" data-edit="filtro_space_term_area" data-original-title="Área de Atuação" data-emptytext="Selecione a(s) área(s) de atuação"></span>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_space_meta_type): ?>
            <p>
              <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_type") && $editEntity? 'required': '');?>">Tipo de Espaço: </span>
              <span class="js-editable" data-edit="filtro_space_meta_type" data-original-title="Tipo de Espaço" data-emptytext="Selecione o(s) tipo(s) de espaço(s)"></span>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_event_term_linguagem): ?>
            <p>
              <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_event_term_linguagem") && $editEntity? 'required': '');?>">Linguagem: </span>
              <span class="js-editable" data-edit="filtro_event_term_linguagem" data-original-title="Linguagem" data-emptytext="Selecione o(s) tipos(s) de linguagem"></span>
            </p>
        <?php endif;?>
    </div>
<?php endif; ?>
