<div class="alert success">
    <?php \MapasCulturais\i::_e("Inscrição enviada no dia");?>
    <?php echo $entity->sentTimestamp->format('d/m/Y à\s H:i:s'); ?>
</div>

<h3 class="registration-header"><?php \MapasCulturais\i::_e("Formulário de Inscrição");?></h3>

<div class="registration-fieldset clearfix">
    <h4><?php \MapasCulturais\i::_e("Número da Inscrição");?></h4>
    <div class="registration-id alignleft">
        <?php echo $entity->number ?>
    </div>
    <div class="alignright">
        <?php if($project->publishedRegistrations): ?>
            <span class="status status-{{getStatusSlug(<?php echo $entity->status ?>)}}">{{getStatusNameById(<?php echo $entity->status ?>)}}</span>
        <?php elseif($project->canUser('@control')): ?>
            <mc-select class="{{getStatusSlug(data.registration.status)}}" model="data.registration" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus"></mc-select>
        <?php endif; ?>
    </div>
</div>
