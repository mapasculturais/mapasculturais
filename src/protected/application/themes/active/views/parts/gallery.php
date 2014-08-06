<?php 
if($this->controller->action === 'create')
    return;
?>
<?php $gallery = $entity->getFiles('gallery'); ?>
<?php if (is_editable() || $gallery): ?>
    <h3>Galeria</h3>
    <div class="clearfix js-gallery">
        <?php if($gallery): foreach($gallery as $img): ?>
            <div id="file-<?php echo $img->id ?>" class="item-da-galeria" >
                <a href="<?php echo $img->url; ?>"><img src="<?php echo $img->transform('galleryThumb')->url; ?>" /></a>
                <?php if(is_editable()): ?>
                <div class="botoes-de-edicao">
                    <a data-href="<?php echo $img->deleteUrl?>" data-target="#file-<?php echo $img->id ?>" class="icone icon_close_alt hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir"></a>
                </div>
                <?php endif; ?>
            </div>
        <?php endforeach; endif;?>
    </div>
    <?php if(is_editable()): ?>
        <p class="p-dos-botoes">
            <a class="botao adicionar js-open-editbox" data-target="#editbox-gallery-image" href="#">adicionar imagem</a>
            <div id="editbox-gallery-image" class="js-editbox mc-top" title="Adicionar Imagem na Galeria">
                <?php add_ajax_uploader($entity, 'gallery', 'append', 'div.js-gallery', '<div id="file-{{id}}" class="item-da-galeria" ><a href="{{url}}"><img src="{{files.galleryThumb.url}}" /></a> <div class="botoes-de-edicao"><a data-href="{{deleteUrl}}" data-target="#file-{{id}}" class="icone icon_close_alt hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir"></a></div></div>', 'galleryThumb')?>
            </div>
        </p>
    <?php endif; ?>
<?php endif; ?>
