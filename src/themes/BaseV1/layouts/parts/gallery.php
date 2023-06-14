<?php
if($this->controller->action === 'create')
    return;
?>
<?php if(!is_object($entity)):?>
    <div class="alert info"><?php MapasCulturais\i::__("Nenhuma imagem disponível");?></div>
    <?php return;?>
<?php endif;?>

<?php $gallery = $entity->getFiles('gallery'); ?>
<?php if(is_array($gallery) && count($gallery) <= 0 && $this->controller == 'registration'):?>
    <div class="alert info"><?php i::__("Nenhuma imagem disponível");?></div>
<?php endif;?>

<?php if ($this->isEditable() || $gallery): ?>
    <h3><?php \MapasCulturais\i::_e("Galeria");?></h3>
    <div class="clearfix js-gallery">
        <?php if($gallery): foreach($gallery as $img): ?>
            <div id="file-<?php echo $img->id ?>" class="image-gallery-item" >
                <a href="<?php echo $img->url; ?>" title="<?php echo htmlspecialchars($img->description, ENT_QUOTES); ?>"><img src="<?php echo $img->transform('galleryThumb')->url; ?>" /></a>
                <?php if($this->isEditable()): ?>

                    <a data-href="<?php echo $img->deleteUrl?>" data-target="#file-<?php echo $img->id ?>" class="btn btn-default delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="<?php \MapasCulturais\i::esc_attr_e("Excluir");?>"></a>

                <?php endif; ?>
            </div>
        <?php endforeach; endif;?>
    </div>
    <?php if($this->isEditable()): ?>
        <p class="gallery-footer">
            <a class="btn btn-default add js-open-editbox" data-target="#editbox-gallery-image" href="#" rel='noopener noreferrer'><?php \MapasCulturais\i::_e("Adicionar imagem");?></a>
            <div id="editbox-gallery-image" class="js-editbox mc-top" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar Imagem na Galeria");?>">
                <?php $this->ajaxUploader($entity, 'gallery', 'append', 'div.js-gallery', '<div id="file-{{id}}" class="image-gallery-item" ><a href="{{url}}" rel="noopener noreferrer"><img src="{{files.galleryThumb.url}}" /></a> <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" class="btn btn-default delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title='. \MapasCulturais\i::__("Excluir").' rel="noopener noreferrer"></a></div>', 'galleryThumb', true)?>
            </div>
        </p>
    <?php endif; ?>
<?php endif; ?>
