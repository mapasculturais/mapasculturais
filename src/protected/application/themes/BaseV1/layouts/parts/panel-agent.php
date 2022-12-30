<?php
use MapasCulturais\Entities\Agent;

$_type = is_object($entity->type) ? $entity->type->name : "";
?>
<article class="objeto clearfix <?php if($entity->isUserProfile) echo 'agent-default'; ?>">
    <?php
        $can_edit = true;
        $app->applyHook('can-edit', ['can_edit' => &$can_edit, 'entity' => $entity]);
    ?>
    <h1>
        <?php if($entity->isUserProfile): ?>
            <a class="icon icon-agent hltip active js-disable" title="<?php \MapasCulturais\i::esc_attr_e("Este é seu perfil.");?>"></a>
            <span style="float: right;font-size: 15px;"><?php \MapasCulturais\i::esc_attr_e("Meu Perfil");?></span>
        <?php elseif($entity->status === Agent::STATUS_ENABLED && $can_edit): ?>
            <a class="icon icon-agent hltip" title="<?php \MapasCulturais\i::esc_attr_e("Definir este agente como seu perfil.");?>" href="<?php echo $app->createUrl('agent', 'setAsUserProfile', array($entity->id)); ?>"></a>
        <?php endif; ?>

        <a href="<?php if($can_edit) echo $entity->singleUrl; else echo "http://culturaviva.gov.br/cadastrar"; ?>"><?php echo htmlentities($entity->name); ?></a>
    </h1>
    <div class="objeto-meta">
        <?php $this->applyTemplateHook('panel-new-fields-before','begin', [ $entity ]); ?>
        <?php $this->applyTemplateHook('panel-new-fields-before','end'); ?>
        <div><span class="label"><?php \MapasCulturais\i::_e("Tipo:");?></span> <?php echo $_type; ?></div>
        <div><span class="label"><?php \MapasCulturais\i::_e("Área(s) de atuação:");?></span> <?php echo implode(', ', $entity->terms['area'])?></div>
        <?php if(isset($entity->originSiteUrl)): ?>
            <div><span class="label">Url: </span><?php echo $entity->originSiteUrl;?></div>
        <?php endif; ?>
    </div>

    <div class="entity-actions">
        <?php $this->applyTemplateHook('entity-actions','begin', [ $entity ]); ?>
        <?php
        if(!$can_edit){
            ?>
            <a href="http://culturaviva.gov.br/cadastrar" target="_blank" rel='noopener noreferrer'>Usuário criado na rede cultura viva</a>
            <?php
        }else{
        ?>
        <a class="btn btn-small btn-primary" href="<?php echo $entity->editUrl; ?>"><?php \MapasCulturais\i::_e("editar");?></a>
        <?php if(!$entity->isUserProfile && !isset($only_edit_button)): ?>

            <?php if($entity->status === Agent::STATUS_ENABLED): ?>
                <?php if($entity->canUser('remove')): ?>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>
                <?php endif; ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->archiveUrl; ?>"><?php \MapasCulturais\i::_e("arquivar");?></a>

            <?php elseif ($entity->status === Agent::STATUS_DRAFT): ?>
                <a class="btn btn-small btn-warning" href="<?php echo $entity->publishUrl; ?>"><?php \MapasCulturais\i::_e("publicar");?></a>
                <a class="btn btn-small btn-danger" href="<?php echo $entity->deleteUrl; ?>"><?php \MapasCulturais\i::_e("excluir");?></a>

            <?php elseif ($entity->status === \MapasCulturais\Entities\Agent::STATUS_ARCHIVED): ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

            <?php elseif ($entity->status === \MapasCulturais\Entities\Agent::STATUS_ARCHIVED): ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->unarchiveUrl; ?>"><?php \MapasCulturais\i::_e("desarquivar");?></a>

            <?php else: ?>
                <a class="btn btn-small btn-success" href="<?php echo $entity->undeleteUrl; ?>"><?php \MapasCulturais\i::_e("recuperar");?></a>
                <?php if($entity->canUser('destroy')): ?>
                    <a class="btn btn-small btn-danger" href="<?php echo $entity->destroyUrl; ?>"><?php \MapasCulturais\i::_e("excluir definitivamente");?></a>
                <?php endif; ?>
            <?php endif; ?>
        <?php endif; ?>
        <?php } ?>
        <?php $this->applyTemplateHook('entity-actions','end', [ $entity ]); ?>
    </div>

</article>
