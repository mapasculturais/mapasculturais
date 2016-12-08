<?php $editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';?>
<div id="entidades" class="aba-content">
    <p class="alert info"> Nesta seção você configura as entidades que estarão habilitadas na instalação e as cores para cada uma das entidades.</p>
    <?php if($this->isEditable() || $entity->entidades_habilitadas): ?>
        <p>
            <span class="label">Entidades Habilitadas: </span>
            <editable-multiselect entity-property="entidades_habilitadas" empty-label="Selecione" allow-other="false" box-title="Entidades habilitadas:"></editable-multiselect>
        </p>
    <?php endif; ?>
        <style>
            .colors {
                margin-bottom: 2em;
            }

            .colors .color {
                float: left;
                margin:0 20px;
            }

            .colors .editable {
                display:block;
                color: transparent;
                overflow: hidden;
                border-radius: 50px;
                width:50px;
                height:50px;
                margin:0;
                padding:0;
                border: 1px dashed #aaa;
                cursor: pointer;
            }
        </style>
    <div class="colors">
        <span class="label">Cores: </span> <br>

        <div class="color">
            <label>Introdução Site</label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_intro") && $editEntity? 'required': '');?>" data-edit="cor_intro" data-original-title="Introdução Site" data-emptytext="Introdução do Site" data-type="color"><?php echo $entity->cor_intro; ?></span>
        </div>

        <div class="color">
            <label>Desenvolvedores</label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_dev") && $editEntity? 'required': '');?>" data-edit="cor_dev" data-original-title="Desenvolvedores" data-emptytext="Desenvolvedores" data-type="color"><?php echo $entity->cor_dev; ?></span>
        </div>
        </br>
        </br>
        </br>
        </br>
        <div class="color">
            <label>Agentes</label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_agentes") && $editEntity? 'required': '');?>" data-edit="cor_agentes" data-original-title="Agentes" data-emptytext="Agentes" data-type="color"><?php echo $entity->cor_agentes; ?></span>
        </div>

        <div class="color">
            <label>Espaços</label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_espacos") && $editEntity? 'required': '');?>" data-edit="cor_espacos" data-original-title="Espaços" data-emptytext="Espaços" data-type="color"><?php echo $entity->cor_espacos; ?></span>
        </div>

        <div class="color">
            <label>Projetos</label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_projetos") && $editEntity? 'required': '');?>" data-edit="cor_projetos" data-original-title="Projetos" data-emptytext="Projetos" data-type="color"><?php echo $entity->cor_projetos; ?></span>
        </div>

        <div class="color">
            <label>Eventos</label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_eventos") && $editEntity? 'required': '');?>" data-edit="cor_eventos" data-original-title="Eventos" data-emptytext="Eventos" data-type="color"><?php echo $entity->cor_eventos; ?></span>
        </div>

        <div class=" clear"></div>
    </div>
</div>
