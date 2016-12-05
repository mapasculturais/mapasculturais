<article class="objeto clearfix">
    <?php if(isset($entity->{'@files:avatar.avatarSmall'}) && $avatar = $entity->{'@files:avatar.avatarSmall'}): ?>
        <div class="thumb" style="background-image: url(<?php echo $avatar->url; ?>)"></div>
    <?php else: ?>
        <div class="thumb"></div>
    <?php endif; ?>
    <h1><a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a></h1>
	<div class="objeto-meta">
                <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $entity ]); ?>
                <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
                <?php if($entity->type): ?>
		<div><span class="label"><?php \MapasCulturais\i::_e("Tipo:");?></span> <?php echo $entity->type->name?></div>
                <?php endif; ?>
        <?php $areas = isset($entity->terms->area)?$entity->terms->area: $entity->terms['area'];?>
		<div><span class="label"><?php \MapasCulturais\i::_e("Área(s) de atuação:");?></span> <?php echo implode(', ', $areas)?></div>
		<div><span class="label"><?php \MapasCulturais\i::_e("Local:");?></span> <?php echo $entity->endereco?></div>
		<div><span class="label"><?php \MapasCulturais\i::_e("Acessibilidade:");?></span> <?php echo $entity->acessibilidade ? $entity->acessibilidade : \MapasCulturais\i::_e('Não informado') ?></div>
        <?php $createTimestamp = isset($entity->createTimestamp->date)? (new DateTime($entity->createTimestamp->date))->format('d/m/Y H:i:s'): $entity->createTimestamp->format('d/m/Y H:i:s'); ?>
		<div><span class="label"><?php \MapasCulturais\i::_e("Data de Criação:");?></span> <?php echo $createTimestamp; ?></div>
	</div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>"><?php \MapasCulturais\i::_e("editar");?></a>

        <?php if($entity->status === \MapasCulturais\Entities\Space::STATUS_ENABLED): ?>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
            <a class="btn btn-small btn-success" href="<?php echo $entity->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

        <?php elseif ($entity->status === \MapasCulturais\Entities\Space::STATUS_DRAFT): ?>
            <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
            <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
            
        <?php elseif ($entity->status === \MapasCulturais\Entities\Space::STATUS_ARCHIVED): ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>
        <?php else: ?>
            <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
            <?php if($entity->canUser('destroy')): ?>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
