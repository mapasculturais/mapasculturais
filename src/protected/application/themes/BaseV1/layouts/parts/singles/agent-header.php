
    <header class="main-content-header">
        <div class="ficha-spcultura">
            <?php $this->part('singles/header-image', ['entity' => $entity]); ?>
            
            <?php $this->part('singles/entity-status', ['entity' => $entity]); ?>
            
            <div class="header-container-card">
                <div class="header-content edit-card">
                    <?php $this->applyTemplateHook('header-content','begin'); ?>
                    
                    <div class="edit-card-header">
                        <div class="edit-card-header-avatar">
                            <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--agent.png']); ?>
                        </div>
                        <div class="edit-card-header-body">
                            <?php $this->part('singles/type', ['entity' => $entity]) ?>
                            <?php $this->part('singles/name', ['entity' => $entity]) ?>
                            <div class="widget-items">
                                <hr>
                                <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
                            </div>
                        </div>
                    </div>
                </div>  
                <div class="edit-card-header-widgets">
                    
                    <?php $this->part('widget-tags', array('entity'=>$entity)); ?>
                </div>
                
                
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 2000): ?>
                    <div class="alert warning">
                        <?php \MapasCulturais\i::_e("O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui");?>
                        <?php echo strlen($entity->shortDescription) ?>
                        <?php \MapasCulturais\i::_e("caracteres. Você deve alterar seu texto ou este será cortado ao salvar.");?>
                    </div>
                <?php endif; ?>
                <div class="widget">
                    <h3 class="label <?php echo ($entity->isPropertyRequired($entity,"shortDescription") && $this->isEditable()? 'required': '');?>"><?php \MapasCulturais\i::_e("Descrição curta:");?></h3>
                    <span class="js-editable" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta");?>" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                </div>
                <?php if($this->isEditable() || $entity->site): ?>
                <div class="widget">
                    <h3><?php \MapasCulturais\i::_e("Site:");?></h3>
                    <?php if($this->isEditable()): ?>
                        <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"site") && $this->isEditable()? 'required': '');?>" data-edit="site" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Site");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira a url de seu site");?>"><?php echo $entity->site; ?></span></p>
                    <?php else: ?>
                        <a class="url" href="<?php echo $entity->site; ?>"><?php echo $entity->site; ?></a>
                    <?php endif; ?>
                </div>
                <?php endif; ?> 
                <?php $this->part('redes-sociais', array('entity'=>$entity));?>
                <?php $this->applyTemplateHook('header-content','end'); ?>
                <?php $this->applyTemplateHook('header-content','after'); ?>
            </div>
        </div>                            
    </header>
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>