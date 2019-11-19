<?php
use MapasCulturais\i;

if(!$this->isEditable()){
    return;
}

$owner_email = $opportunity->owner->emailPrivado ? $opportunity->owner->emailPrivado : $opportunity->owner->emailPublico;
?>
<div id="registration-claim-configuration" class="registration-fieldset project-edit-mode">
    <h4><?php i::_e("Formulário para recursos");?></h4>

    <p>
        <span class="js-editable" data-edit="claimDisabled" data-original-title="<?php i::esc_attr_e('Formulário de recursos');?>" data-value="<?php echo $opportunity->claimDisabled ?>"></span>
    </p>
    <p>
        <span class="label"><?php i::_e("Email de destino dos recursos");?>: </span><br>
        <span class="js-editable" data-edit="claimEmail" data-original-title="<?php i::esc_attr_e('Email do destinatário');?>" data-emptytext="<?php echo $owner_email;?>"><?php echo $opportunity->claimEmail; ?></span>
    </p>
</div>
