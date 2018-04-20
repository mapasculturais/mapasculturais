<?php $editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';?>
<div id="entidades" class="aba-content">
    <p class="alert info"> <?php \MapasCulturais\i::_e("Nesta seção você configura as entidades que estarão habilitadas na instalação e as cores para cada uma das entidades.");?></p>
    <?php if($this->isEditable() || $entity->entidades_habilitadas): ?>
        <p>
            <span class="label"><?php \MapasCulturais\i::_e("Entidades Habilitadas:");?> </span>
            <editable-multiselect entity-property="entidades_habilitadas" empty-label="<?php \MapasCulturais\i::esc_attr_e('Selecione');?>" allow-other="false" box-title="<?php \MapasCulturais\i::esc_attr_e('Entidades habilitadas:');?>"></editable-multiselect>
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
        <span class="label"><?php \MapasCulturais\i::_e("Cores:");?> </span> <br>

        <div class="color">
            <label><?php \MapasCulturais\i::_e("Introdução Site");?></label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_intro") && $editEntity? 'required': '');?>" data-edit="cor_intro" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Introdução Site');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Introdução do Site');?>" data-type="color"><?php echo $entity->cor_intro; ?></span>
        </div>

        <div class="color">
            <label><?php \MapasCulturais\i::_e("Desenvolvedores");?></label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"cor_dev") && $editEntity? 'required': '');?>" data-edit="cor_dev" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Desenvolvedores');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Desenvolvedores');?>" data-type="color"><?php echo $entity->cor_dev; ?></span>
        </div>
        </br>
        </br>
        </br>
        </br>
        <div class="color">
            <label><?php \MapasCulturais\i::_e("Agentes");?></label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"agents_color") && $editEntity? 'required': '');?>" data-edit="agents_color" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Agentes');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Agentes');?>" data-type="color"><?php echo $entity->agents_color; ?></span>
        </div>

        <div class="color">
            <label><?php \MapasCulturais\i::_e("Espaços");?></label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"spaces_color") && $editEntity? 'required': '');?>" data-edit="spaces_color" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Espaços');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Espaços');?>" data-type="color"><?php echo $entity->spaces_color; ?></span>
        </div>

        <div class="color">
            <label><?php \MapasCulturais\i::_e("Projetos");?></label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"project_color") && $editEntity? 'required': '');?>" data-edit="projects_color" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Projetos');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Projetos');?>" data-type="color"><?php echo $entity->projects_color; ?></span>
        </div>

        <div class="color">
            <label><?php \MapasCulturais\i::_e("Eventos");?></label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"events_color") && $editEntity? 'required': '');?>" data-edit="events_color" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Eventos');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Eventos');?>" data-type="color"><?php echo $entity->events_color; ?></span>
        </div>

        <div class="color">
            <label><?php \MapasCulturais\i::_e("Oportunidades");?></label>
            <span class="js-editable inline js-color <?php echo ($entity->isPropertyRequired($entity,"opportunities_color") && $editEntity? 'required': '');?>" data-edit="opportunities_color" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Oportunidades');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Oportunidades');?>" data-type="color"><?php echo $entity->opportunities_color; ?></span>
        </div>

        <div class=" clear"></div>
    </div>
</div>
