<?php
    $action = preg_replace("#^(\w+/)#", "", $this->template);
    $this->bodyProperties['ng-app'] = "entity.app";
    $this->bodyProperties['ng-controller'] = "EntityController";
    $this->addEntityToJs($relation);

    if($this->isEditable()){
        $this->addEntityTypesToJs($relation);
    }

    $this->includeMapAssets();
    $this->includeAngularEntityAssets($relation);
    $entity = $relation->seal;
?>

<article class="main-content seal">
    <!-- exibição dos avatares do selo e do agente -->
    <div class="display-seal-relation">
        <div class="seal-avatar">
            <a href="<?php echo $entity->getSingleUrl(); ?>">
                <?php $this->part('singles/avatar-seal', ['entity' => $entity, 'size'=> 'avatarMedium', 'default_image' => 'img/avatar--seal.png']); ?>
            </a>
            <?php if($this->isEditable()): ?>
                <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-avatar" href="#"><?php \MapasCulturais\i::_e("Editar");?></a>
                <div id="editbox-change-avatar" class="js-editbox mc-right" title="<?php \MapasCulturais\i::esc_attr_e("Editar avatar");?>">
                    <?php $this->ajaxUploader ($entity, 'avatar', 'image-src', 'div.avatar img.js-avatar-img', '', 'avatarBig'); ?>
                </div>
            <?php endif; ?>
        </div>
        <div class="agent-avatar">
            <a href="<?php echo $relation->owner_relation->getSingleUrl(); ?>" >
                <?php $this->part('singles/avatar-seal', ['entity' => $relation->owner_relation, 'size'=> 'avatarMedium', 'default_image' => 'img/avatar--seal.png']); ?>
            </a>                    
        </div>
    </div>

    <!-- exibe o nome e data de expiração(se disponível) do selo visualizado -->    
        <div id="seal-info-container">
            <div id="seal-name">
                <?php $this->applyTemplateHook('name','before'); ?>
                <h2><span class="js-editable" data-edit="name" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>"><a href="<?php echo $app->createUrl('seal', 'single', ['id' => $entity->id])?>"><?php echo $entity->name; ?></a></span></h2>
                <?php $this->applyTemplateHook('name','after'); ?>
            </div>
            <div id="expiration-date">
            <div>
                    <?php if($relation->seal->validPeriod > 0):?>
                        <span class="label">
                                <?php if($expirationDate['expirated']): ?>
                                <?php \MapasCulturais\i::_e('Expirado em:'); ?>

                                <?php else:?>
                                    <?php \MapasCulturais\i::_e('V&aacute;lido at&eacute;:'); ?>
                                <?php endif;?>
                        </span>
                        <span class="js-editable" data-edit="validateDate" data-original-title="Data de Validade" data-emptytext="">
                        <?php echo $expirationDate['date']->format('d-m-Y'); ?></span>&nbsp;   
                    <?php endif; ?>
                
                <?php if($relation->seal->owner->userId <> $app->user->id): ?>
                    <?php if(!$relation->renovation_request && ($expirationDate['expirated'] && $app->config['notifications.seal.toExpire'] > 0)):?>
                        <a href="<?php echo $relation->getRequestSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                            <?php \MapasCulturais\i::_e("Solicitar renovação");?>
                        </a>
                    <?php elseif($relation->renovation_request && ($diff <= 0 && $diff <= $app->config['notifications.seal.toExpire'])):?>
                        <div class="alert warning">
                            <?php \MapasCulturais\i::_e("Renovação Solicitada");?>
                        <!--</div>-->
                    <?php endif;?>
                <?php elseif($entity->owner->userId == $app->user->id && ($diff <= 0 && $diff <= $app->config['notifications.seal.toExpire'])):?>
                    <a href="<?php echo $relation->getRenewSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                        <?php \MapasCulturais\i::_e("Renovar selo");?>
                    </a>
                <?php endif;?>
            </div>        
    
    <!--.validade-->    
        </div>
    </div>
    <!--print seal relation -->
    <div id="seal-print-container">
        
    </div>
            
    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <div class="ficha-spcultura">
                <p>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta");?>" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </p>
            </div>
            <!--.ficha-spcultura-->

            <?php if ( $entity->longDescription ): ?>
                <h3><?php \MapasCulturais\i::_e("Descrição");?></h3>
                <span class="descricao js-editable" data-edit="longDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição do Selo");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição do selo");?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>
            <!--.descricao-->

        </div>
        <!-- #sobre -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after'); ?>
	<?php $this->part('owner', array('entity' => $relation, 'owner' => $relation->owner_relation)); ?>
</article>