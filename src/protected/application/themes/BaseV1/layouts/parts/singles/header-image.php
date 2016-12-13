<?php 
if ($header = $entity->getFile('header')){
    $style = "background-image: url({$header->transform('header')->url});";
} else {
    $style = "";
}
?>
<?php $this->applyTemplateHook('header-image','before'); ?>
<div class="header-image js-imagem-do-header" style="<?php echo $style ?>">        
    <?php if ($this->isEditable()): ?>
        <a class="btn btn-default edit js-open-editbox" data-target="#editbox-change-header" href="#"><?php \MapasCulturais\i::_e("Editar");?></a>
        <div id="editbox-change-header" class="js-editbox mc-bottom" title="<?php \MapasCulturais\i::esc_attr_e("Editar Imagem da Capa");?>">
            <?php $this->ajaxUploader($entity, 'header', 'background-image', '.js-imagem-do-header', '', 'header'); ?>
        </div>
    <?php endif; ?>
</div>
<?php $this->applyTemplateHook('header-image','after'); ?>
