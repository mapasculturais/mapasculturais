<?php $downloads = $entity->getFiles('downloads'); ?>
<?php if ($this->isEditable() || $downloads): ?>
    <div class="widget">
        <h3 class="<?php if($this->isEditable()) echo \MapasCulturais\i::_e('editando') ?>">Downloads</h3>
        <?php if($this->isEditable()): ?>
            <a class="add js-open-editbox hltip" data-target="#editbox-download-file" href="#" title="<?php \MapasCulturais\i::esc_attr_e("Clique para adicionar arquivo para download");?>"></a>
            <div id="editbox-download-file" class="js-editbox mc-left" title="<?php \MapasCulturais\i::esc_attr_e("Adicionar Arquivo");?>" data-submit-label="Enviar">
                <?php $this->ajaxUploader($entity, 'downloads', 'append', 'ul.js-downloads', '<li id="file-{{id}}" class="widget-list-item"><a href="{{url}}">{{description}}</a> <div class="botoes"><a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este vídeo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo"></a></div></li>', '', true, false)?>
            </div>
        <?php endif; ?>
        <ul class="widget-list js-downloads js-slimScroll">
            <?php if(is_array($downloads)): foreach($downloads as $download): ?>
                <li id="file-<?php echo $download->id ?>" class="widget-list-item<?php if($this->isEditable()) echo \MapasCulturais\i::_e(' is-editable'); ?>" >
                    <a href="<?php echo $download->url;?>"><span><?php echo $download->description ? $download->description : $download->name;?></span></a>
                    <?php if($this->isEditable()): ?>
                        <div class="botoes">
                        <a data-href="<?php echo $download->deleteUrl?>" data-target="#file-<?php echo $download->id ?>" data-configm-message="<?php \MapasCulturais\i::esc_attr_e('Remover este vídeo?');?>" class="delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="<?php \MapasCulturais\i::esc_attr_e("Excluir arquivo");?>"></a>
                        </div>
                    <?php endif; ?>
                </li>
            <?php endforeach; endif;?>
        </ul>
    </div>
<?php endif; ?>
