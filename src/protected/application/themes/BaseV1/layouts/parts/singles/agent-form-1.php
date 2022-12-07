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

$birthday = null;
if($entity->dataDeNascimento){
    $today = new DateTime('now');
    $calc = (new DateTime($entity->dataDeNascimento))->diff($today);
    $birthday = ($calc->y >= 60) ? $calc->y : null;

}

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
                    <span class="label"><?php \MapasCulturais\i::_e("Nome Social");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"nomeSocial") && $editEntity? 'required': '');?>" data-edit="nomeSocial" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome Social");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe seu nome social");?>">
                        <?php echo $entity->nomeSocial; ?>
                    </span>
                </p>
                  <!-- Campo Nome Completo -->
                  <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Nome Completo");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"nomeCompleto") && $editEntity? 'required': '');?>" data-edit="nomeCompleto" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome Completo ou Razão Social");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Preencha o nome de registro");?>">
                        <?php echo $entity->nomeCompleto; ?>
                    </span>
                </p>
                  <!-- Campo CPF -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("CPF");?>:</span>
                    <span class="js-editable js-mask-cpf <?php echo ($entity->isPropertyRequired($entity,"documento") && $editEntity? 'required': '');?>" data-edit="documento" data-original-title="<?php \MapasCulturais\i::esc_attr_e("CPF");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe seu CPF  com pontos, hífens e barras");?>">
                        <?php echo $entity->documento; ?>
                    </span>
                </p>
                  <!-- Campo Mei -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("CNPJ (MEI)");?>:</span>
                    <span class="js-editable js-mask-cnpj <?php echo ($entity->isPropertyRequired($entity,"cnpj") && $editEntity? 'required': '');?>" data-edit="cnpj" data-original-title="<?php \MapasCulturais\i::esc_attr_e("CNPJ");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Caso seja Micro Empreendedor Individual informe seu CNPJ");?>">
                        <?php echo $entity->cnpj; ?>
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
                <!-- Campo Comunidades tradicionais -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Comunidades tradicionais");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"comunidadesTradicional") && $editEntity? 'required': '');?>" data-edit="comunidadesTradicional" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Pertence alguma comunidade tradicional?");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Pertence alguma comunidade tradicional?");?>">
                        <?php echo $entity->comunidadesTradicional; ?>
                    </span>
                </p>
                <!-- Campo Outras Comunidades tradicionais -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Não encontrou sua comunidade Tradicional?");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"comunidadesTradicionalOutros") && $editEntity? 'required': '');?>" data-edit="comunidadesTradicionalOutros" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Informe outra comunidade tradicional?");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Preencha aqui");?>">
                        <?php echo $entity->comunidadesTradicionalOutros; ?>
                    </span>
                </p>
                  <!-- Campo Pessoa Deficiênte -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Pessoa com Deficiência");?>:</span>
                    <editable-multiselect entity-property="pessoaDeficiente" empty-label="Selecione uma ou mais opções" box-title="Possui alguma deficiência ?" help-text="Selecione abaixo."></editable-multiselect>
                </p>
                  <!-- Campo Escolaridade -->
                  <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Escolaridade");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"escolaridade") && $editEntity? 'required': '');?>" data-edit="escolaridade" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Informe sua Escolaridade");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe o seu nível escolar");?>">
                        <?php echo $entity->escolaridade; ?>
                    </span>
                </p>
                <!-- Campo Data de Nascimento  -->
                <p class="privado">
                    <span class="icon icon-private-info"></span>
                    <span class="label"><?php \MapasCulturais\i::_e("Data de Nascimento");?>:</span>
                    <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"dataDeNascimento") && $this->isEditable()? 'required': '');?>" <?php echo $entity->dataDeNascimento ? "data-value='".$entity->dataDeNascimento . "'" : ''?>  data-type="date" data-edit="dataDeNascimento" data-viewformat="dd/mm/yyyy" data-showbuttons="false" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Data de Nascimento");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira a data de nascimento");?>">
                    <?php $dtN = (new DateTime)->createFromFormat('Y-m-d', $entity->dataDeNascimento); echo $dtN ? $dtN->format('d/m/Y') : ''; ?>
                    </span>
                </p>
                  <!-- Campo Pessoas idosa -->
                  <?php if(!is_null($entity->idoso)):?>
                 <p class="privado">
                     <span class="icon icon-private-info"></span>
                     <span class="label"><?php \MapasCulturais\i::_e("Pessoa idosa (a cima de 60 anos)");?>:</span>
                     <?php $entity->idoso ? \MapasCulturais\i::_e("SIM") : \MapasCulturais\i::_e("NÂO") ?>
                </p>
                <?php endif;?>
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