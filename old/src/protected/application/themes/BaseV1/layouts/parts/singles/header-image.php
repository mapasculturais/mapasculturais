<?php 
if ($header = $entity->getFile('header')){    
    $style = "background-image: url({$header->transform('header')->url});";
    $hasBackgroundImage = true;
    $removeBackgroundButtonClass = 'display-background-button';
    $removeBackgroundButtonUrl = $header->deleteUrl;
} else {
    $style = "background-image: url('');";
    $hasBackgroundImage = false;
    $removeBackgroundButtonClass = 'hide-background-button';
    $removeBackgroundButtonUrl = '';
}
?>
<?php $this->applyTemplateHook('header-image','before'); ?>
<?php if ($this->isEditable() || $header): ?>
<div id="header-banner" class="header-image js-imagem-do-header" style="<?php echo $style ?>">        
    <?php if ($this->isEditable()): ?>
        <a class="btn btn-default edit js-open-editbox " data-target="#editbox-change-header" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Editar");?></a>
        <div id="editbox-change-header" class="js-editbox mc-bottom" title="<?php \MapasCulturais\i::esc_attr_e("Editar Imagem da Capa");?>">
            <?php $this->ajaxUploader($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header', false, [850,192]); ?>
        </div>
    <?php endif; ?>
    <?php if($this->isEditable()): ?>
        <div id="remove-background-button" class="<?php echo $removeBackgroundButtonClass; ?>">            
            <a class="btn btn-default delete banner-delete button-position-delete" title="<?php \MapasCulturais\i::esc_attr_e("Excluir capa");?>" 
                data-href="<?php echo $removeBackgroundButtonUrl; ?>">Excluir</a>
        </div>
    <?php endif; ?>
</div>
<?php endif; ?>
<?php $this->applyTemplateHook('header-image','after'); ?>
