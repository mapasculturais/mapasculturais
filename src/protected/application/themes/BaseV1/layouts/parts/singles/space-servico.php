<div class="servico">
    <?php $this->applyTemplateHook('tab-about-service','begin'); ?>

    <?php if($this->isEditable()): ?>
        <p style="display:none" class="privado"><span class="icon icon-private-info"></span><?php \MapasCulturais\i::_e("Virtual ou Físico? (se for virtual a localização não é obrigatória)");?></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->acessibilidade): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade");?>: </span><span class="js-editable" data-edit="acessibilidade" data-original-title="Acessibilidade"><?php echo $entity->acessibilidade; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->acessibilidade_fisica): ?>
    <p>
        <span class="label"><?php \MapasCulturais\i::_e("Acessibilidade física");?>: </span>
        <editable-multiselect entity-property="acessibilidade_fisica" empty-label="Selecione" allow-other="true" box-title="Acessibilidade física:"></editable-multiselect>
    </p>
    <?php endif; ?>
    <?php $this->applyTemplateHook('acessibilidade','after'); ?>

    <?php if($this->isEditable() || $entity->capacidade): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Capacidade");?>: </span><span class="js-editable" data-edit="capacidade" data-original-title="Capacidade" data-emptytext="Especifique a capacidade <?php $this->dict('entities: of the space') ?>"><?php echo $entity->capacidade; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->horario): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Horário de funcionamento");?>: </span><span class="js-editable" data-edit="horario" data-original-title="Horário de Funcionamento" data-emptytext="Insira o horário de abertura e fechamento"><?php echo $entity->horario; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->site): ?>
        <p><span class="label"><?php \MapasCulturais\i::_e("Site");?>:</span>
        <?php if($this->isEditable()): ?>
            <span class="js-editable" data-edit="site" data-original-title="Site" data-emptytext="Insira a url de seu site"><?php echo $entity->site; ?></span></p>
        <?php else: ?>
            <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
        <?php endif; ?>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->emailPublico): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Email Público");?>:</span> <span class="js-editable" data-edit="emailPublico" data-original-title="Email Público" data-emptytext="Insira um email que será exibido publicamente"><?php echo $entity->emailPublico; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable()):?>
        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php \MapasCulturais\i::_e("Email Privado");?>:</span> <span class="js-editable" data-edit="emailPrivado" data-original-title="Email Privado" data-emptytext="Insira um email que não será exibido publicamente"><?php echo $entity->emailPrivado; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable() || $entity->telefonePublico): ?>
    <p><span class="label"><?php \MapasCulturais\i::_e("Telefone Público");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefonePublico" data-original-title="Telefone Público" data-emptytext="Insira um telefone que será exibido publicamente"><?php echo $entity->telefonePublico; ?></span></p>
    <?php endif; ?>

    <?php if($this->isEditable()):?>
        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php \MapasCulturais\i::_e("Telefone Privado 1");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefone1" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone1; ?></span></p>
        <p class="privado"><span class="icon icon-private-info"></span><span class="label"><?php \MapasCulturais\i::_e("Telefone Privado 2");?>:</span> <span class="js-editable js-mask-phone" data-edit="telefone2" data-original-title="Telefone Privado" data-emptytext="Insira um telefone que não será exibido publicamente"><?php echo $entity->telefone2; ?></span></p>
    <?php endif; ?>
    <?php $this->applyTemplateHook('tab-about-service','end'); ?>
</div>