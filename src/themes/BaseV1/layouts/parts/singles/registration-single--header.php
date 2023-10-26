<?php $sentDate = $entity->sentTimestamp; ?>

<h3 class="registration-header"><?php \MapasCulturais\i::_e("Formulário de Inscrição");?></h3>

<?php $this->applyTemplateHook('header-fieldset', 'before');?>
<div class="registration-fieldset clearfix">

    <?php $this->applyTemplateHook('header-fieldset', 'begin');?>
    <?php if ($sentDate): ?>
        <div class="alert success">
            <?= sprintf(\MapasCulturais\i::__("Obrigado por utilizar o %s. Suas informações foram enviadas com sucesso no dia"), $this->dict('site: name', false));?> 
            <?php echo $sentDate->format(\MapasCulturais\i::__('d/m/Y à\s H:i:s')); ?>
        </div>
    <?php endif; ?>

    <h4><?php \MapasCulturais\i::_e("Número da Inscrição");?></h4>
    <div class="registration-id alignleft">
        <?php echo $entity->number ?>        
    </div>

    <div class="alignright">
        <?php if($entity->canUser('changeStatus')): ?>
            <mc-select class="{{getStatusSlug(data.registration.status)}}" model="data.registration" data="data.registrationStatusesNames" getter="getRegistrationStatus" setter="setRegistrationStatus"></mc-select>
        <?php elseif($opportunity->publishedRegistrations): ?>
            <span class="status status-{{getStatusSlug(<?php echo $entity->status ?>)}}">{{getStatusNameById(<?php echo $entity->status ?>)}}</span>
        <?php endif; ?>        
    </div>        
    <?php $this->applyTemplateHook('header-fieldset', 'end');?>

</div>
<?php $this->applyTemplateHook('header-fieldset', 'after');?>

<?php if($entity->projectName): ?>
    <div class="registration-fieldset" ng-if="data.avaliableEvaluationFields['projectName'] || (data.entity.userHasControl)">
        <div class="label"><?php \MapasCulturais\i::_e("Nome do Projeto"); ?> </div>
        <h5> {{data.entity.object.projectName}} </h5>
    </div>
<?php endif; ?>
