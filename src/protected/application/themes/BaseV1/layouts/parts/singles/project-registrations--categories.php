<?php 
$can_edit = $entity->canUser('modifyRegistrationFields');

$ditable_class = $can_edit ? 'js-editable' : '';

if ($can_edit) {
    $registration_categories = $entity->registrationCategories ? implode("\n", $entity->registrationCategories) : '';
} else {
    $registration_categories = is_array($entity->registrationCategories) ? implode('; ', $entity->registrationCategories) : '';
}
?>
<div id="registration-categories" class="registration-fieldset">
    <h4>3. <?php \MapasCulturais\i::_e("Opções");?></h4>
    <p ng-if="data.entity.canUserModifyRegistrationFields" class="registration-help"><?php \MapasCulturais\i::_e('É possível criar opções para os proponentes escolherem na hora de se inscrever, como, por exemplo, "categorias" ou "modalidades". Se não desejar utilizar este recurso, deixe em branco o campo "Opções".');?></p>
    <p ng-if="!data.entity.canUserModifyRegistrationFields" class="registration-help"><?php \MapasCulturais\i::_e("A edição destas opções estão desabilitadas porque agentes já se inscreveram neste projeto.");?> </p>
    <p>
        <span class="label"><?php \MapasCulturais\i::_e("Título das opções");?></span><br>
        <span class="<?php echo $ditable_class ?>" data-edit="registrationCategTitle" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Título das opções");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira um título para o campo de opções");?>"><?php echo $entity->registrationCategTitle ? $entity->registrationCategTitle : \MapasCulturais\i::__('Categoria'); ?></span>
    </p>
    <p>
        <span class="label"><?php \MapasCulturais\i::_e("Descrição das opções");?></span><br>
        <span class="<?php echo $ditable_class ?>" data-edit="registrationCategDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição das opções");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição para o campo de opções");?>"><?php echo $entity->registrationCategDescription ? $entity->registrationCategDescription : \MapasCulturais\i::__('Selecione uma categoria'); ?></span>
    </p>
    <p>
        <span class="label"><?php \MapasCulturais\i::_e("Opções");?></span><br>

        <span class="<?php echo $ditable_class ?> js-categories-values" data-edit="registrationCategories" data-type="textarea" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Opções de inscrição (coloque uma opção por linha)");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira as opções de inscrição");?>"><?php echo $registration_categories; ?></span>
    </p>
</div>
<!-- #registration-categories -->
