<?php
$fieldsList = [
    'personalData' => [
        'nomeCompleto',
        'documento',
        'emailPrivado',
        'emailPublico',
        'telefonePublico',
        'telefone1',
        'telefone2',
    ],
    'sensitiveData' => [
        'dataDeNascimento',
        'genero',
        'orientacaoSexual',
        'agenteItinerante',
        'raca'
    ],
    'location' => [
        'En_CEP',
        'En_Nome_Logradouro',
        'En_Complemento',
        'En_Bairro',
        'En_Municipio',
        'En_Estado'
    ]

];
$this->applyTemplateHook('agent-form-1', 'before', [&$fieldsList]);

$canSee = function ($view) use ($entity, $fieldsList) {

    foreach ($fieldsList[$view] as $_field) {
        if ($entity->$_field) {
            return true;
        }
    }

    return false;
};
?>
<div class="ficha-spcultura">

    <?php $this->applyTemplateHook('tab-about-service','before'); ?><!--. hook tab-about-service:before -->

    <div class="servico">
        <?php $this->applyTemplateHook('tab-about-service','begin'); ?><!--. hook tab-about-service:begin -->
        <?php if($entity->canUser("viewPrivateData")): ?>
            <?php if($this->isEditable() || $canSee('personalData')):?>
                <!-- Campo Nome Completo -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Nome Fantasia");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"nomeCompleto") && $editEntity? 'required': '');?>" data-edit="nomeCompleto" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome Fantasia ou Razão Social");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe o nome fantasia ou razão social");?>">
                        <?php echo $entity->nomeCompleto; ?>
                    </span>
                </p>
                <!-- Campo CNPJ -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php  \MapasCulturais\i::_e("CNPJ");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"documento") && $editEntity? 'required': '');?>" data-edit="documento" data-original-title="<?php \MapasCulturais\i::esc_attr_e("CPF");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe seu CNPJ com pontos, hífens e barras");?>">
                        <?php echo $entity->documento; ?>
                    </span>
                </p>
                
                <!-- Campo Data de Nascimento / Fundação -->

                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Data de Fundação");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"dataDeNascimento") && $this->isEditable()? 'required': '');?>" <?php echo $entity->dataDeNascimento ? "data-value='".$entity->dataDeNascimento . "'" : ''?>  data-type="date" data-edit="dataDeNascimento" data-viewformat="dd/mm/yyyy" data-showbuttons="false" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Data de Nascimento/Fundação");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira a data de nascimento ou fundação do agente");?>">
                        <?php $dtN = (new DateTime)->createFromFormat('Y-m-d', $entity->dataDeNascimento); echo $dtN ? $dtN->format('d/m/Y') : ''; ?>
                    </span>
                </p>
            
                <!-- E-mail privado-->
                <p class="privado"><span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Email Privado");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"emailPrivado") && $editEntity? 'required': '');?>" data-edit="emailPrivado" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Email Privado");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um email que não será exibido publicamente");?>">
                        <?php echo $entity->emailPrivado; ?>
                    </span>
                </p>
            <?php endif; ?>
        <?php endif; ?>
                <!-- Email Público -->
                <?php if($this->isEditable() || $entity->emailPublico): ?>
                    <p><span class="label"><?php \MapasCulturais\i::_e("E-mail");?>:</span>
                        <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"emailPublico") && $this->isEditable()? 'required': '');?>" data-edit="emailPublico" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Email Público");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um email que será exibido publicamente");?>">
                            <?php echo $entity->emailPublico; ?>
                        </span>
                    </p>
                <?php endif; ?>
            <!-- telefone Público -->
            <?php if($this->isEditable() || $entity->telefonePublico): ?>
                <p><span class="label"><?php \MapasCulturais\i::_e("Telefone Público");?>:</span>
                    <span class="js-editable js-mask-phone <?php echo ($entity->isPropertyRequired($entity,"telefonePublico") && $this->isEditable()? 'required': '');?>" data-edit="telefonePublico" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Telefone Público");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um telefone que será exibido publicamente");?>">
                        <?php echo $entity->telefonePublico; ?>
                    </span>
                </p>
            <?php endif; ?>
            <?php if($entity->canUser("viewPrivateData")): ?>
                <?php if($this->isEditable() || $canSee('personalData')):?>
                    <!-- Telefone Privado 1 -->
                    <p class="privado"><span class="icon icon-private-info"></span>
                        <span class="label"><?php \MapasCulturais\i::_e("Telefone 1");?>:</span>
                        <span class="js-editable js-mask-phone <?php echo ($entity->isPropertyRequired($entity,"telefone1") && $this->isEditable()? 'required': '');?>" data-edit="telefone1" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Telefone Privado");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um telefone que não será exibido publicamente");?>">
                            <?php echo $entity->telefone1; ?>
                        </span>
                    </p>
                    <!-- Telefone Privado 2 -->
                    <p class="privado"><span class="icon icon-private-info"></span>
                        <span class="label"><?php \MapasCulturais\i::_e("Telefone 2");?>:</span>
                        <span class="js-editable js-mask-phone <?php echo ($entity->isPropertyRequired($entity,"telefone2") && $this->isEditable()? 'required': '');?>" data-edit="telefone2" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Telefone Privado");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um telefone que não será exibido publicamente");?>">
                            <?php echo $entity->telefone2; ?>
                        </span>
                    </p>
                <?php endif; ?>
            <?php endif; ?>
        
        <?php $this->applyTemplateHook('tab-about-service','end'); ?><!--. hook tab-about-service:end -->
        
        <?php if($this->isEditable() || ($entity->publicLocation && $canSee('location')) ):?>
            <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => true]); ?><!--.part/singles/location.php -->
        <?php endif; ?>

        <?php $this->applyTemplateHook('tab-about-service','end'); ?><!--. hook tab-about-service:end -->
        
    </div><!--.servico -->
</div>
<?php $this->applyTemplateHook('tab-about-service','after'); ?><!--. hook tab-about-service:after -->