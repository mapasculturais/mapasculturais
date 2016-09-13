<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$viewModeString = $entityName !== 'project' ? '' : ',viewMode:list';
$tags = $entity->terms['tag'];
?>
<?php if($this->isEditable() || !empty($tags)): ?>
    <div id="filtros" class="aba-content">
        <span class="label">Filtros:</span>
        <?php if($this->isEditable() || $entity->filtro_uf): ?>
            <p>
              <span class="label">Estado: </span>
              <editable-multiselect entity-property="filtro_uf" empty-label="Selecione o(s) estado(s)" allow-other="true" box-title="Estado:"></editable-multiselect>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_area_atuacao_agente): ?>
            <p>
              <span class="label">Área de Atuação do Agente: </span>
              <editable-multiselect entity-property="filtro_area_atuacao_agente" empty-label="Selecione a(s) área(s) de atuação" allow-other="true" box-title="Área de atuação:"></editable-multiselect>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_espaco): ?>
            <p>
              <span class="label">Tipo de Espaço: </span>
              <editable-multiselect entity-property="filtro_espaco" empty-label="Selecione o(s) tipo(s) de espaço(s)" allow-other="true" box-title="Tipo de Espaço:"></editable-multiselect>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_area_atuacao_espaco): ?>
            <p>
              <span class="label">Área de Atuação do Espaço: </span>
              <editable-multiselect entity-property="filtro_area_atuacao_espaco" empty-label="Selecione a(s) área(s) de atuação" allow-other="true" box-title="Área de atuação:"></editable-multiselect>
            </p>
        <?php endif;?>
        <?php if($this->isEditable() || $entity->filtro_linguagem): ?>
            <p>
              <span class="label">Linguagem: </span>
              <editable-multiselect entity-property="filtro_linguagem" empty-label="Selecione o(s) tipos(s) de linguagem" allow-other="true" box-title="Linguagem:"></editable-multiselect>
            </p>
        <?php endif;?>
    </div>
<?php endif; ?>
