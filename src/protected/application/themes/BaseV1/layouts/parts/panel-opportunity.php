<?php
$registration = ['from' => $entity->registrationFrom, 'to'=> $entity->registrationTo];
$registration_dates = [];

if($registration['from'] instanceof DateTime) {
    $registration_dates['from'] = $registration['from']->format('d/m/Y');
}

if($registration['to'] instanceof DateTime) {
    $registration_dates['to'] = $registration['to']->format('d/m/Y');
}
?>

<article class="objeto clearfix">
    <?php if($avatar = $entity->avatar): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->transform('avatarSmall')->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
	<div class="objeto-meta">
        <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $entity ]); ?>
        <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
		<div> <span class="label">Tipo:</span> <?php echo $entity->type->name?> </div>
        <br>

        <?php if( is_array($registration) && ( $registration['from'] || $registration['to'] ) ): ?>
            <div>
                <span class="label"> <?php \MapasCulturais\i::_e("Inscrições:");?> </span>

                <?php
                if($entity->isRegistrationOpen())
                    \MapasCulturais\i::_e("Abertas ");

                if($registration['from'] && !$registration['to'])
                    echo \MapasCulturais\i::__("a partir de ") . $registration_dates['from'];
                elseif(!$registration['from'] && $registration['to'])
                    echo \MapasCulturais\i::__(' até ') . $registration_dates['to'];
                else
                    echo \MapasCulturais\i::__('de ') . $registration_dates['from'] .\MapasCulturais\i::__(' a '). $registration_dates['to'];
                ?>
            </div>
            <br>
        <?php endif; ?>

        <div><span class="label"><?php \MapasCulturais\i::_e("Organização:");?></span> <?php echo $entity->owner->name; ?></div>
        <?php if($entity->originSiteUrl): ?>
            <div><span class="label">Url: </span> <?php echo $entity->originSiteUrl;?></div>
        <?php endif; ?>
	</div>
    <div class="entity-actions">
        <?php $this->applyTemplateHook('entity-actions','begin', [ $entity ]); ?>
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>"><?php \MapasCulturais\i::_e("editar");?></a>

        <?php if($entity->status === \MapasCulturais\Entities\Opportunity::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
            <a class="btn btn-small btn-success" href="<?php echo $entity->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Opportunity::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Opportunity::STATUS_ARCHIVED): ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Opportunity::STATUS_ARCHIVED): ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

        <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                <?php if($entity->canUser('destroy')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                <?php endif; ?>
        <?php endif; ?>
        <?php $this->applyTemplateHook('entity-actions','end', [ $entity ]); ?>
    </div>
</article>
