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
?>

<article class="main-content seal">
    <!-- exibição dos avatares do selo e do agente -->
    <div class="display-seal-relation">
        <div class="seal-avatar">
            <a href="<?php echo $seal->getSingleUrl(); ?>">
                <?php $this->part('singles/avatar-seal-relation', ['entity' => $seal, 'size'=> 'avatarMedium', 'default_image' => 'img/avatar--seal.png']); ?>
            </a>
        </div>
        <div class="agent-avatar">
            <a href="<?php echo $relation->owner->getSingleUrl(); ?>" >
                <?php $this->part('singles/avatar-seal-relation', ['entity' => $relation->owner, 'size'=> 'avatarMedium', 'default_image' => 'img/avatar--seal.png']); ?>
            </a>                    
        </div>
    </div>
        <div id="seal-info-container">
            <div id="seal-name">
                <?php $this->applyTemplateHook('seal-name','before'); ?>
                <h2>
                    <span class="js-editable" data-edit="name" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>">
                        <a href="<?php echo $app->createUrl('seal', 'single', ['id' => $seal->id])?>"><?php echo $seal->name; ?></a>
                    </span>
                </h2>
                <?php $this->applyTemplateHook('seal-name','after'); ?>
            </div>
            <div id="agent-name">
                <?php $this->applyTemplateHook('agent-name','before'); ?>
                <h5>
                    <span class="js-editable" data-edit="name" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Nome de exibição");?>">
                        <a href="<?php echo $relation->owner->getSingleUrl(); ?>"><?php echo $relation->owner->name; ?></a>
                    </span>
                </h5>
                <?php $this->applyTemplateHook('agent-name','after'); ?>
            </div>
             <!--print seal relation -->
             <?php $this->applyTemplateHook('print-certificate','before'); ?>
            <div id="seal-print-container">
                <?php echo $printSeal ?>
            </div>
            <?php $this->applyTemplateHook('print-certificate','after',[$relation]); ?>
        </div><!-- fim seal info container -->
        <!-- Data de expiração -->
            <?php if($seal->validPeriod > 0):?>
            <div id="expiration-date">
                <?php if($seal->isExpired()): ?>
                    <?php \MapasCulturais\i::_e('<b>Expirado em:</b>'); ?>

                    <?php else:?>
                        <?php \MapasCulturais\i::_e('<b>Válido até:</b>'); ?>
                    <?php endif;?>
                    <?php echo $relation->validateDate->format('d/m/Y'); ?>
                <?php endif; ?>
                <?php if($seal->owner->userId <> $app->user->id): ?>
                    <?php if(!$relation->renovation_request && $relation->isExpired() && $app->config['notifications.seal.toExpire']):?>
                        <a href="<?php echo $relation->getRequestSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                            <?php \MapasCulturais\i::_e("Solicitar renovação");?>
                        </a>
                    <?php elseif($relation->renovation_request && $relation->isExpired() && $app->config['notifications.seal.toExpire']):?>
                        <div class="alert warning">
                            <?php \MapasCulturais\i::_e("Renovação Solicitada");?>
                        <!--</div>-->
                    <?php endif;?>
                <?php elseif($seal->owner->userId == $app->user->id && $relation->isExpired() && $app->config['notifications.seal.toExpire']):?>
                    <a href="<?php echo $relation->getRenewSealRelationUrl($relation->id);?>" class="btn btn-default js-toggle-edit">
                        <?php \MapasCulturais\i::_e("Renovar selo");?>
                    </a>
                <?php endif;?>
            </div>
</article>
