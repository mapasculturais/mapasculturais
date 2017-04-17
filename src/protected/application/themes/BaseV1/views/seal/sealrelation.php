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
                <?php $this->part('singles/avatar-seal', ['entity' => $entity, 'size'=> 'avatarBig', 'default_image' => 'img/avatar--seal.png']); ?>
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
        <div id="seal-info-container">
            <div id="seal-name">
                <?php $this->applyTemplateHook('name','before'); ?>
                <h2><span class="js-editable" data-edit="name" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>"><a href="<?php echo $app->createUrl('seal', 'single', ['id' => $entity->id])?>"><?php echo $entity->name; ?></a></span></h2>
                <?php $this->applyTemplateHook('name','after'); ?>
            </div>
            <!-- Data de expiração -->
            <div id="expiration-date">
                    <?php if($relation->seal->validPeriod > 0):?>
                        <span class="label">
                                <?php if($expirationDate['expired']): ?>
                                <?php \MapasCulturais\i::_e('Expirado em:'); ?>

                                <?php else:?>
                                    <?php \MapasCulturais\i::_e('V&aacute;lido at&eacute;:'); ?>
                                <?php endif;?>
                        </span>
                        <span class="js-editable" data-edit="validateDate" data-original-title="Data de Validade" data-emptytext="">
                        <?php echo $expirationDate['date']->format('d-m-Y'); ?></span>&nbsp;   
                    <?php endif; ?>
                
                <?php if($relation->seal->owner->userId <> $app->user->id): ?>
                    <?php if(!$relation->renovation_request && $expirationDate['expired'] && $app->config['notifications.seal.toExpire']):?>
                        <a href="<?php echo $relation->getRequestSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                            <?php \MapasCulturais\i::_e("Solicitar renovação");?>
                        </a>
                    <?php elseif($relation->renovation_request && $expirationDate['expired'] && $app->config['notifications.seal.toExpire']):?>
                        <div class="alert warning">
                            <?php \MapasCulturais\i::_e("Renovação Solicitada");?>
                        <!--</div>-->
                    <?php endif;?>
                <?php elseif($entity->owner->userId == $app->user->id && ($expirationDate['expired'] && $app->config['notifications.seal.toExpire'])):?>
                    <a href="<?php echo $relation->getRenewSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                        <?php \MapasCulturais\i::_e("Renovar selo");?>
                    </a>
                <?php endif;?>
            </div>        
             <!--print seal relation -->
            <div id="seal-print-container">
                <?php echo $printSeal ?>
            </div>        
        </div><!-- fim seal info container -->
</article>