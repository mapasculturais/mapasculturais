<?php $downloads = $entity->getFiles('downloads'); ?>
<?php if (is_editable() || $downloads): ?>
    <div class="bloco">
        <h3 class="subtitulo <?php if(is_editable()) echo 'editando' ?>">Downloads</h3>
        <?php if(is_editable()): ?>
            <a class="adicionar js-open-dialog hltip" data-dialog="#dialog-download-file" href="#" title="Clique para adicionar arquivo para download"></a>
            <div id="dialog-download-file" class="js-dialog" title="Adicionar Arquivo">
                <?php add_ajax_uploader($entity, 'downloads', 'append', 'ul.js-downloads', '<li id="file-{{id}}" class="li-dos-blocos"><a href="{{url}}">{{description}}</a> <div class="botoes"><a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este vídeo?" class="icone icon_close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo"></a></div></li>', '', true)?>
            </div>
        <?php endif; ?>
        <ul class="ul-dos-blocos js-downloads js-slimScroll">
            <?php if(is_array($downloads)): foreach($downloads as $download): ?>
                <li id="file-<?php echo $download->id ?>" <?php if(is_editable()) echo 'class="li-dos-blocos"' ?>>
                    <a href="<?php echo $download->url;?>"><?php echo $download->description ? $download->description : $download->name;?></a>
                    <?php if(is_editable()): ?>
                        <div class="botoes">
                        <a data-href="<?php echo $download->deleteUrl?>" data-target="#file-<?php echo $download->id ?>" data-configm-message='Remover este vídeo?' class="icone icon_close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo"></a>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; endif;?>
        </ul>
    </div>
<?php endif; ?>