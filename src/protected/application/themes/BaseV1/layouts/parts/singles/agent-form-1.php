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
    <?php if($this->isEditable() || $canSee('personalData') || ($entity->publicLocation && $canSee('location'))):?>
    <h3><?php \MapasCulturais\i::_e("Dados Pessoais");?></h3>
    <?php endif; ?>

    <?php $this->applyTemplateHook('tab-about-service','before'); ?>

    <div class="servico">
        <?php $this->applyTemplateHook('tab-about-service','begin'); ?>
        <?php if($entity->canUser("viewPrivateData")): ?>
            <?php if($this->isEditable() || $canSee('personalData')):?>
                <!-- Campo Nome Social -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Sub-área de atuação");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"nomeSocial") && $editEntity? 'required': '');?>" data-edit="nomeSocial" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome Social");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Sub-área de atuação");?>">
                        <?php echo $entity->nomeSocial; ?>
                    </span>
                </p>
                 <!-- Campo Nome Social -->
                 <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Nome Social");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"nomeSocial") && $editEntity? 'required': '');?>" data-edit="nomeSocial" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome Social");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe seu nome social");?>">
                        <?php echo $entity->nomeSocial; ?>
                    </span>
                </p>
                <!-- Campo Função -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Função");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"funcao") && $editEntity? 'required': '');?>" data-edit="funcao" data-original-title="<?php \MapasCulturais\i::esc_attr_e("função");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe sua função");?>">
                        <?php echo $entity->funcao; ?>
                    </span>
                </p>
                  <!-- Campo Mei -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("MEI");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"mei") && $editEntity? 'required': '');?>" data-edit="mei" data-original-title="<?php \MapasCulturais\i::esc_attr_e("MEI");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Enquadra-se como MEI?");?>">
                        <?php echo $entity->mei; ?>
                    </span>
                </p>
                  <!-- Campo Escolaridade -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Escolaridade");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"escolaridade") && $editEntity? 'required': '');?>" data-edit="escolaridade" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Informe sua Escolaridade");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe o seu nível escolar");?>">
                        <?php echo $entity->escolaridade; ?>
                    </span>
                </p>
                  <!-- Campo Pessoa Deficiênte -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Pessoa com Deficiência");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"pessoaDeficiente") && $editEntity? 'required': '');?>" data-edit="pessoaDeficiente" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Possui alguma deficiência?");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Possui alguma deficiência?");?>">
                        <?php echo $entity->pessoaDeficiente; ?>
                    </span>
                </p>
                  <!-- Campo Comunidades tradicionais -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Comunidades tradicionais");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"comunidadesTradicionais") && $editEntity? 'required': '');?>" data-edit="comunidadesTradicionais" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Pertence alguma comunidade tradicional?");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Pertence alguma comunidade tradicional?");?>">
                        <?php echo $entity->comunidadesTradicionais; ?>
                    </span>
                </p>
                 <!-- Campo Pessoas idosa -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Pessoa idosa (a cima de 60 anos)");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"pessoaIdosa") && $editEntity? 'required': '');?>" data-edit="pessoaIdosa" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Possui acima de 60 anos de idade?");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Possui acima de 60 anos de idade?");?>">
                        <?php echo $entity->pessoaIdosa; ?>
                    </span>
                </p>
                <!-- Campo Nome Completo -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Nome Completo");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"nomeCompleto") && $editEntity? 'required': '');?>" data-edit="nomeCompleto" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome Completo ou Razão Social");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe seu nome completo ou razão social");?>">
                        <?php echo $entity->nomeCompleto; ?>
                    </span>
                </p>
                <!-- Campo CPF -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("CPF");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"documento") && $editEntity? 'required': '');?>" data-edit="documento" data-original-title="<?php \MapasCulturais\i::esc_attr_e("CPF");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe seu CPF  com pontos, hífens e barras");?>">
                        <?php echo $entity->documento; ?>
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
            <p><span class="label"><?php \MapasCulturais\i::_e("E-mail Público");?>:</span>
                <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"emailPublico") && $this->isEditable()? 'required': '');?>" data-edit="emailPublico" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Email Público");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um email que será exibido publicamente");?>">
                    <?php echo $entity->emailPublico; ?>
                </span>
            </p>
        <?php endif; ?>
        
        <!-- Telefone Público -->
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
        
        <?php if($this->isEditable() || ($entity->publicLocation && $canSee('location')) ):?>
        <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => true]); ?><!--.part/singles/location.php -->
        <?php endif; ?>

        <?php $this->applyTemplateHook('tab-about-service','end'); ?>
    
    </div><!--.servico -->

    <?php $this->applyTemplateHook('tab-about-service','after'); ?><!--. hook tab-about-service:after -->

</div>
<?php if($entity->canUser("viewPrivateData")): ?>
    <?php if($this->isEditable() || $canSee('sensitiveData')):?>
        <div class="ficha-spcultura"> 
            <h3><?php \MapasCulturais\i::_e("Dados Pessoais Sensíveis");?></h3>
            <div class="servico">
                <!-- Campo Data de Nascimento  -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Data de Nascimento");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"dataDeNascimento") && $this->isEditable()? 'required': '');?>" <?php echo $entity->dataDeNascimento ? "data-value='".$entity->dataDeNascimento . "'" : ''?>  data-type="date" data-edit="dataDeNascimento" data-viewformat="dd/mm/yyyy" data-showbuttons="false" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Data de Nascimento");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira a data de nascimento");?>">
                    <?php $dtN = (new DateTime)->createFromFormat('Y-m-d', $entity->dataDeNascimento); echo $dtN ? $dtN->format('d/m/Y') : ''; ?>
                    </span>
                </p>
                <!-- Gênero -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Gênero");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"genero") && $editEntity? 'required': '');?>" data-edit="genero" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Gênero");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione o gênero se for pessoa física");?>">
                        <?php echo $entity->genero; ?>
                    </span>
                </p>
                <!-- Orientação Sexual -->
                <p class="privado"><span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Orientação Sexual");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"orientacaoSexual") && $editEntity? 'required': '');?>" data-edit="orientacaoSexual" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Orientação Sexual");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione uma orientação sexual se for pessoa física");?>">
                        <?php echo $entity->orientacaoSexual; ?>
                    </span>
                </p> 

                 <!-- Agente Itinerante -->
                 <p class="privado"><span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Agente Itinerante");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"agenteItinerante") && $editEntity? 'required': '');?>" data-edit="agenteItinerante" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Agente Itinerante");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Responda sim, caso seja agente Itinerante ou não se possuir residência fixa");?>">
                        <?php echo $entity->agenteItinerante; ?>
                    </span>
                </p> 
            </p> 
                </p> 
                <!-- Raça/Cor -->
                <p class="privado"><span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Raça/Cor");?>:</span>
                    <span class="js-editable  <?php echo ($entity->isPropertyRequired($entity,"raca") && $editEntity? 'required': '');?>" data-edit="raca" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Raça/cor");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Selecione a raça/cor se for pessoa física");?>">
                        <?php echo $entity->raca; ?>
                    </span>
                </p>
            </div>
        </div>
    <?php endif; ?>
<?php endif; ?>