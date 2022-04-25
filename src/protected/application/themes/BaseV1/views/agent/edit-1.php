<?php
$action = 'edit'; 
$this->bodyProperties['ng-app'] = "entity.app";
$this->bodyProperties['ng-controller'] = "EntityController";

$this->addEntityToJs($entity);

if($this->isEditable()){
    $this->addEntityTypesToJs($entity);
    $this->addTaxonoyTermsToJs('area');
    $this->addTaxonoyTermsToJs('tag');
}

$this->includeMapAssets();
$this->includeAngularEntityAssets($entity);
$editEntity = $this->controller->action === 'create' || $this->controller->action === 'edit';

?>

<?php $this->applyTemplateHook('breadcrumb','begin'); ?>

<?php $this->part('singles/breadcrumb', ['entity' => $entity,'entity_panel' => 'agents','home_title' => 'entities: My Agents']); ?><!--.part/singles/breadcrumb.php -->

<?php $this->applyTemplateHook('breadcrumb','end'); ?>

<?php $this->part('editable-entity', array('entity'=>$entity, 'action'=>$action)); ?><!--.part/editable-entity.php -->

<article class="main-content agent">
    <?php $this->applyTemplateHook('main-content','begin'); ?>
        
    <header class="main-content-header">
        <div class="ficha-spcultura">
            <?php $this->part('singles/header-image', ['entity' => $entity]); ?><!--.part/singles/header-image.php -->
            
            <?php $this->part('singles/entity-status', ['entity' => $entity]); ?><!--.part/singles/entity-status.php -->
            <h3><?php \MapasCulturais\i::_e("Cartão de visitas");?></h3>
            <!-- <div> inicio card branco-->
            
            <div class="header-content">

                <?php $this->applyTemplateHook('header-content','begin'); ?>
                
                <?php $this->part('singles/avatar', ['entity' => $entity, 'default_image' => 'img/avatar--agent.png']); ?><!--.part/singles/avatar.php -->
                
                <?php $this->part('singles/type', ['entity' => $entity]) ?><!--.part/singles/type.php -->
                
                <?php $this->part('singles/name', ['entity' => $entity]) ?><!--.part/singles/name.php -->
                
                <?php $this->part('widget-areas', array('entity'=>$entity)); ?>
                <?php if($this->isEditable() && $entity->shortDescription && strlen($entity->shortDescription) > 2000): ?>
                    <div class="alert warning">
                        <?php \MapasCulturais\i::_e("O limite de caracteres da descrição curta foi diminuido para 400, mas seu texto atual possui");?>
                        <?php echo strlen($entity->shortDescription) ?>
                        <?php \MapasCulturais\i::_e("caracteres. Você deve alterar seu texto ou este será cortado ao salvar.");?>
                    </div>
                <?php endif; ?>
                <span class="js-editable <?php echo ($entity->isPropertyRequired($entity,"shortDescription") && $editEntity? 'required': '');?>" data-edit="shortDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição Curta");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição curta");?>" data-showButtons="bottom" data-tpl='<textarea maxlength="400"></textarea>'><?php echo $this->isEditable() ? $entity->shortDescription : nl2br($entity->shortDescription); ?></span>
                <?php $this->applyTemplateHook('header-content','end'); ?>
                
                <?php if ($this->isEditable() || $entity->twitter || $entity->facebook || $entity->instagram || $entity->linkedin || $entity->spotify || $entity->youtube || $entity->pinterest): ?>
                <div class="widget">
                    <h3><?php \MapasCulturais\i::_e("Seguir");?></h3>
                    <?php if ($this->isEditable() || $entity->twitter): ?>
                        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
                            <a class="icon icon-twitter js-editable" data-edit="twitter" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Twitter");?>"
                            href="<?php echo $entity->twitter ? $entity->twitter : '#" onclick="return false; ' ?>"
                            data-value="<?php echo $entity->twitter ?>"></a>
                        </span>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->facebook): ?>
                        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
                            <a class="icon icon-facebook js-editable" data-edit="facebook" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Facebook");?>"
                            href="<?php echo $entity->facebook ? $entity->facebook : '#" onclick="return false; ' ?>"
                            data-value="<?php echo $entity->facebook ?>"></a>
                        </span>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->instagram): ?>
                        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
                            <a class="icon icon-instagram js-editable" data-edit="instagram" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Instagram");?>"
                            href="<?php echo $entity->instagramUrl; ?>"
                            data-value="<?php echo $entity->instagram; ?>"></a>
                        </span>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->linkedin): ?>
                        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
                            <a class="icon icon-linkedin js-editable" data-edit="linkedin" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Linkedin");?>"
                            href="<?php echo $entity->linkedin; ?>"
                            data-value="<?php echo $entity->linkedin; ?>"></a>
                        </span>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->spotify): ?>
                        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
                            <a class="icon icon-spotify js-editable" data-edit="spotify" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Spotify");?>"
                            href="<?php echo $entity->spotify; ?>"
                            data-value="<?php echo $entity->spotify; ?>"></a>
                        </span>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->youtube): ?>
                        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
                            <a class="icon icon-youtube js-editable" data-edit="youtube" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no YouTube");?>"
                            href="<?php echo $entity->youtube; ?>"
                            data-value="<?php echo $entity->youtube; ?>"></a>
                        </span>
                    <?php endif; ?>

                    <?php if ($this->isEditable() || $entity->pinterest): ?>
                        <span <?php if($this->isEditable()):?> class="editable-social" <?php endif; ?> >
                        <a class="icon icon-pinterest js-editable" data-edit="pinterest" data-notext="true" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Perfil no Pinterest");?>"
                        href="<?php echo $entity->pinterest; ?>"
                        data-value="<?php echo $entity->pinterest; ?>"></a>
                        </span>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
                <?php $this->applyTemplateHook('header-content','after'); ?>

            </div>
        </div> <!--spcultura-->
    </header>
    
    <!--.main-content-header-->
    <?php $this->applyTemplateHook('header','after'); ?>

    <?php $this->applyTemplateHook('tabs','before'); ?>
    <ul class="abas clearfix clear">
        <?php $this->applyTemplateHook('tabs','begin'); ?>
        <li class="active"><a href="#sobre" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Sobre");?></a></li>
        <?php if(!($this->controller->action === 'create')):?>
        <li><a href="#permissao" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Responsáveis");?></a></li>
        <?php endif;?>
        <?php $this->applyTemplateHook('tabs','end'); ?>
    </ul>
    <?php $this->applyTemplateHook('tabs','after'); ?>

    <div class="tabs-content">
        <?php $this->applyTemplateHook('tabs-content','begin'); ?>
        <div id="sobre" class="aba-content">
            <?php $this->applyTemplateHook('tab-about','begin'); ?>
    
            <?php $this->part('singles/agent-form-1', ['entity' => $entity, 'editEntity' => $editEntity]); ?><!--.part/singles/agent-form.php -->
            
            <?php $this->part('singles/location', ['entity' => $entity, 'has_private_location' => true]); ?><!--.part/singles/location.php -->
           
           <div>
           <h4><strong><?php \MapasCulturais\i::_e("Outras informações públicas");?></strong></h4>
           <p><?php \MapasCulturais\i::_e("Assim como o cartão de visitas, os dados abaixo também serão exibidos para quem visitar o seu perfil.");?></p>
           <hr>
            <?php if ( $this->isEditable() || $entity->longDescription ): ?>
                <h3><?php \MapasCulturais\i::_e("Descrição");?></h3>
                <span class="descricao js-editable <?php echo ($entity->isPropertyRequired($entity,"longDescription") && $this->isEditable()? 'required': '');?>" data-edit="longDescription" data-original-title="<?php \MapasCulturais\i::esc_attr_e("Descrição do Agente");?>" data-emptytext="<?php \MapasCulturais\i::esc_attr_e("Insira uma descrição do agente");?>" ><?php echo $this->isEditable() ? $entity->longDescription : nl2br($entity->longDescription); ?></span>
            <?php endif; ?>

                <?php $this->part('video-gallery.php', array('entity'=>$entity)); ?><!--.part/video-gallery.php -->

                <?php $this->part('gallery.php', array('entity'=>$entity)); ?><!--.part/gallery.php -->
            </div>
            <?php $this->applyTemplateHook('tab-about','end'); ?>
        </div>
        <!-- #sobre -->
        <?php $this->part('singles/permissions') ?><!--.part/singles/permissions.php -->

        <?php $this->applyTemplateHook('tabs-content','end'); ?>
    </div>
    <!-- .tabs-content -->
    <?php $this->applyTemplateHook('tabs-content','after');?>
    
    <?php $this->part('owner', array('entity' => $entity, 'owner' => $entity->owner)); ?><!--.part/owner.php -->

    <?php $this->applyTemplateHook('main-content','end'); ?>
</article>
        <div class="sidebar-left sidebar agent">
            <!-- Related Seals BEGIN -->
            <?php $this->part('related-seals.php', array('entity'=>$entity)); ?> 
            <!-- Related Seals END -->
                <!-- tags -->
            <?php $this->part('widget-tags', array('entity'=>$entity)); ?> 
            
           <!-- ?php $this->part('redes-sociais', array('entity'=>$entity)); ?>  --> 
                
        </div> 
<div class="sidebar agent sidebar-right">
    <?php if($this->controller->action == 'create'): ?>
        <div class="widget">
            <p class="alert info"><?php \MapasCulturais\i::_e("Para adicionar arquivos para download ou links, primeiro é preciso salvar o agente.");?><span class="close"></span></p>
        </div>
    <?php endif; ?>

    <!-- Related Admin Agents BEGIN -->
        <?php $this->part('related-admin-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Admin Agents END -->

    <!-- Related Agents BEGIN -->
        <?php $this->part('related-agents.php', array('entity'=>$entity)); ?>
    <!-- Related Agents END -->

    <!-- Children BEGIN -->
        <?php $this->part('singles/list-entities.php', array('entities'=>$entity->spaces, 'title' => 'entities: Spaces of the agent')); ?>
    <!-- Children END -->

    <!-- Relations Groups BEGIN -->
        <?php $this->part('singles/list-relations.php', array('entities'=>$entity)); ?>
    <!-- Relations Groups END -->
    
    <!-- Children BEGIN -->
        <?php $this->part('singles/list-entities.php', array('entities'=>$entity->projects, 'title' => 'entities: Projects of the agent')); ?>
    <!-- Children END -->

    <!-- Children BEGIN -->
        <?php $this->part('singles/list-entities.php', array('entities'=>$entity->children, 'title' => 'entities: Agent children')); ?>
    <!-- Children END -->

    <!-- Downloads BEGIN -->
        <?php $this->part('downloads.php', array('entity'=>$entity)); ?>
    <!-- Downloads END -->

    <!-- Link List BEGIN -->
        <?php $this->part('link-list.php', array('entity'=>$entity)); ?>
    <!-- Link List END -->

    <!-- History BEGIN -->
        <?php $this->part('history.php', array('entity' => $entity)); ?>
    <!-- History END -->
</div>
