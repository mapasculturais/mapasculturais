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
        <?php if($opportunity->publishedRegistrations): ?>
            <span class="status status-{{getStatusSlug(<?php echo $entity->status ?>)}}">{{getStatusNameById(<?php echo $entity->status ?>)}}</span>
        <?php endif; ?>
    </div>
</div>

<div class="registration-fieldset">
    <?php if($entity->projectName): ?>
        <div class="label"><?php \MapasCulturais\i::_e("Nome do Projeto"); ?> </div>
        <h5> <?php echo $entity->projectName; ?> </h5>
    <?php endif; ?>
</div>
