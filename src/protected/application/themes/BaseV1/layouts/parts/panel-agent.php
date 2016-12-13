<?php
use MapasCulturais\Entities\Agent;
?>
<article class="objeto clearfix">
    <h1>
        <?php if($entity->isUserProfile): ?>
            <a class="icon icon-agent hltip active js-disable" title="<?php \MapasCulturais\i::esc_attr_e("Este é seu agente padrão.");?>"></a>
        <?php elseif($entity->status === Agent::STATUS_ENABLED): ?>
            <a class="icon icon-agent hltip" title="<?php \MapasCulturais\i::esc_attr_e("Definir este agente como meu agente padrão.");?>" href="<?php echo $app->createUrl('agent', 'setAsUserProfile', array($entity->id)); ?>"></a>
        <?php endif; ?>
        <a href="<?php echo $entity->singleUrl; ?>"><?php echo $entity->name; ?></a>
    </h1>
    <div class="objeto-meta">
        <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $entity ]); ?>
        <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
        <div><span class="label"><?php \MapasCulturais\i::_e("Tipo:");?></span> <?php echo $entity->type->name?></div>
        <div><span class="label"><?php \MapasCulturais\i::_e("Área(s) de atuação:");?></span> <?php echo implode(', ', $entity->terms['area'])?></div>
    </div>
    <div class="entity-actions">
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>"><?php \MapasCulturais\i::_e("editar");?></a>
        <?php if(!$entity->isUserProfile): ?>

            <?php if($entity->status === Agent::STATUS_ENABLED): ?>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
                <a class="btn btn-small btn-success" href="<?php echo $entity->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

            <?php elseif ($entity->status === Agent::STATUS_DRAFT): ?>
                <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>

            <?php elseif ($entity->status === \MapasCulturais\Entities\Agent::STATUS_ARCHIVED): ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

            <?php else: ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                <?php if($entity->canUser('destroy')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
    </div>
</article>
