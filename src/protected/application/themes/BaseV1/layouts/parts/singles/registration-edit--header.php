<h3 class="registration-header"><?php \MapasCulturais\i::_e("Formulário de Inscrição");?></h3>
<p class="registration-help"><?php \MapasCulturais\i::_e("Itens com asterisco são obrigatórios.");?></p>
<div class="registration-fieldset">
    <h4><?php \MapasCulturais\i::_e("Número da Inscrição");?></h4>
    <div class="registration-id">
        <?php if($action !== 'create'): ?><?php echo $entity->number ?><?php endif; ?>
    </div>
</div>
<?php
$opportunity = $entity->opportunity;
if ($opportunity->projectName):
    ?>
    <div class="registration-fieldset">
        <div class="label"><?php \MapasCulturais\i::_e("Nome do Projeto"); ?> <?php if ($opportunity->projectName == 2) echo "*" ?></div>
        
        <h4 class='js-editable-field js-include-editable' id="projectName" data-name="projectName" data-type="text" data-original-title="<?php \MapasCulturais\i::_e("Nome do Projeto"); ?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Informe o nome do projeto"); ?>" data-value="<?php echo $entity->projectName ?>"><?php echo $entity->projectName ?></span>
    </div>
<?php endif; ?>