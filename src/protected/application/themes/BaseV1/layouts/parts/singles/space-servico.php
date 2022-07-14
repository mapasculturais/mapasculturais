<div >
    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

    <?php if($this->isEditable()): ?>
        <p style="display:none" class="privado"><span class="icon icon-private-info"></span><?php \MapasCulturais\i::_e("Virtual ou Físico? (se for virtual a localização não é obrigatória)");?></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->acessibilidade): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade");?>: </span><span class="js-editable" data-edit="acessibilidade" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Acessibilidade');?>"><?php echo $entity->acessibilidade; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->acessibilidade_fisica): ?>
    <p>
        <span class="label"><?php \MapasCulturais\i::_e("Acessibilidade física");?>: </span>
        <editable-multiselect entity-property="acessibilidade_fisica" empty-label="<?php \MapasCulturais\i::esc_attr_e('Selecione');?>" allow-other="true" box-title="<?php \MapasCulturais\i::esc_attr_e('Acessibilidade física:');?>"></editable-multiselect>
    </p>
    <?php endif; ?>
    <?php $this->applyTemplateHook('acessibilidade','after'); ?>

    <?php if($this->isEditable() || $entity->capacidade): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Capacidade");?>: </span><span class="js-editable" data-edit="capacidade" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Capacidade');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Especifique a capacidade');?> <?php $this->dict('entities: of the space');?>"><?php echo $entity->capacidade; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->horario): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Horário de funcionamento");?>: </span><span class="js-editable" data-edit="horario" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Horário de Funcionamento');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Insira o horário de abertura e fechamento');?>"><?php echo $entity->horario; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->emailPublico): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Email Público");?>:</span> <span class="js-editable" data-edit="emailPublico" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Email Público');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Insira um email que será exibido publicamente');?>"><?php echo $entity->emailPublico; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable()):?>
        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php \MapasCulturais\i::_e("Email Privado");?>:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Email Privado');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Insira um email que não será exibido publicamente');?>"><?php echo $entity->emailPrivado; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->telefonePublico): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Telefone Público");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Telefone Público');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Insira um telefone que será exibido publicamente');?>"><?php echo $entity->telefonePublico; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable()):?>
        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php \MapasCulturais\i::_e("Telefone Privado 1");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Telefone Privado');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Insira um telefone que não será exibido publicamente');?>"><?php echo $entity->telefone1; ?></span></p>
        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php \MapasCulturais\i::_e("Telefone Privado 2");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="<?php \MapasCulturais\i::esc_attr_e('Telefone Privado');?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e('Insira um telefone que não será exibido publicamente');?>"><?php echo $entity->telefone2; ?></span></p>
    <?php endif; ?>
    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
</div>