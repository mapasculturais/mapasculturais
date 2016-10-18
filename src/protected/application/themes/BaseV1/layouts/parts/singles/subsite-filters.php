<?php
$entityClass = $entity->getClassName();
$entityName = strtolower(array_slice(explode('\\', $entityClass),-1)[0]);
$viewModeString = $entityName !== 'project' ? '' : ',viewMode:list';

$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';

function printSubsiteFilter($property){
    if($property){
        echo implode('; ', $property);
    }
}

?>

<div id="filtros" class="aba-content">
    <style>
        section.filter-section {
            margin-bottom: 1.5em;

        }

        section.filter-section header {
            border-bottom:1px solid #bbb;
            margin-bottom:.5em;
            font-size: 1em;
            text-transform:uppercase;
            font-weight:bold;
        }

    </style>
    <section class="filter-section">
        <header>Agentes</header>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_term_area") && $editEntity? 'required': '');?>">Área de Atuação do Agente: </span>
          <span class="js-editable" data-edit="filtro_agent_term_area" data-original-title="Área de Atuação" data-emptytext="Selecione a(s) área(s) de atuação"><?php printSubsiteFilter($entity->filtro_agent_term_area) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Estado") && $editEntity? 'required': '');?>">Estado(s): </span>
          <span class="js-editable" data-edit="filtro_agent_meta_En_Estado" data-original-title="Estado(s)" data-emptytext="Selecione o(s) estado(s) para o(s) Agente(s)"><?php printSubsiteFilter($entity->filtro_agent_meta_En_Estado) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Municipio") && $editEntity? 'required': '');?>">Municipio(s): </span>
          <span class="js-editable" data-edit="filtro_agent_meta_En_Municipio" data-original-title="Município" data-emptytext="Selecione o(s) município(s) para o(s) Agente(s)"><?php printSubsiteFilter($entity->filtro_agent_meta_En_Municipio) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_agent_meta_En_Bairro") && $editEntity? 'required': '');?>">Bairro(s): </span>
          <span class="js-editable" data-edit="filtro_agent_meta_En_Bairro" data-original-title="Bairro" data-emptytext="Selecione o(s) bairro(s) para o(s) Agente(s)"><?php printSubsiteFilter($entity->filtro_agent_meta_En_Bairro) ?></span>
        </p>
    </section>

    <section class="filter-section">
        <header>Espaços</header>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_term_area") && $editEntity? 'required': '');?>">Área de Atuação do Espaço: </span>
          <span class="js-editable" data-edit="filtro_space_term_area" data-original-title="Área de Atuação" data-emptytext="Selecione a(s) área(s) de atuação"><?php printSubsiteFilter($entity->filtro_space_term_area) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_type") && $editEntity? 'required': '');?>">Tipo de Espaço: </span>
          <span class="js-editable" data-edit="filtro_space_meta_type" data-original-title="Tipo de Espaço" data-emptytext="Selecione o(s) tipo(s) de espaço(s)"><?php printSubsiteFilter($entity->filtro_space_meta_type) ?></span>
        </p>

        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_En_Estado") && $editEntity? 'required': '');?>">Estado: </span>
          <span class="js-editable" data-edit="filtro_space_meta_En_Estado" data-original-title="Estado" data-emptytext="Selecione o(s) estado(s) para o(s) Espaço(s)"><?php printSubsiteFilter($entity->filtro_space_meta_En_Estado) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_En_Municipio") && $editEntity? 'required': '');?>">Municipio(s): </span>
          <span class="js-editable" data-edit="filtro_space_meta_En_Municipio" data-original-title="Município" data-emptytext="Selecione o(s) município(s) para o(s) Agente(s)"><?php printSubsiteFilter($entity->filtro_space_meta_En_Municipio) ?></span>
        </p>
        <p>
          <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_space_meta_En_Bairro") && $editEntity? 'required': '');?>">Bairro(s): </span>
          <span class="js-editable" data-edit="filtro_space_meta_En_Bairro" data-original-title="Bairro" data-emptytext="Selecione o(s) bairro(s) para o(s) Agente(s)"><?php printSubsiteFilter($entity->filtro_space_meta_En_Bairro) ?></span>
        </p>
    </section>

    <section class="filter-section">
        <header>Eventos</header>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"filtro_event_term_linguagem") && $editEntity? 'required': '');?>">Linguagem: </span>
            <span class="js-editable" data-edit="filtro_event_term_linguagem" data-original-title="Linguagem" data-emptytext="Selecione o(s) tipos(s) de linguagem"><?php printSubsiteFilter($entity->filtro_event_term_linguagem) ?></span>
        </p>
    </section>
    
    <section class="filter-section">
        <header>Selos Verificadores</header>
        <p>
            <span class="label <?php echo ($entity->isPropertyRequired($entity,"verifiedSeals") && $editEntity? 'required': '');?>">Selos: </span>
            <span class="js-editable" data-edit="verifiedSeals" data-original-title="Selos Verificadores" data-emptytext="Informe os ids dos selos verificadores separados por ponto e vírgula"><?php printSubsiteFilter($entity->verifiedSeals) ?></span>
        </p>
    </section>
</div>

