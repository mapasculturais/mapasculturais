<?php 
if ($header = $entity->getFile('header')){    
    $style = "background-image: url({$header->transform('header')->url});";
    $hasBackgroundImage = true;
} else {
    $style = "background-image: url('');";
    $hasBackgroundImage = false;
}
?>
<?php $this->applyTemplateHook('header-image','before'); ?>
<?php if ($this->isEditable() || $header): ?>
<div id="header-banner" class="header-image" style="<?php echo $style ?>">        
    <?php if ($this->isEditable()): ?>
        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-header" href="#"><?php \MapasCulturais\i::_e("Editar");?></a>
        <div id="editbox-change-header" class="js-editbox mc-bottom" title="<?php \MapasCulturais\i::esc_attr_e("Editar Imagem da Capa");?>">
            <?php $this->ajaxUploader($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
        </div>
    <?php endif; ?>
    <?php if($this->isEditable() && $hasBackgroundImage): ?>
        <div class="remove-background">            
            <a class="btn btn-default delete banner-delete" title="<?php \MapasCulturais\i::esc_attr_e("Excluir capa");?>" 
                data-href="<?php echo $header->deleteUrl; ?>"></a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php $this->applyTemplateHook('header-image','after'); ?>