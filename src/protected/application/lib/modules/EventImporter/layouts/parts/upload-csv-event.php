<?php 
$app = MapasCulturais\App::i();
$url = $app->createUrl('eventimporter','uploadFile', ['opportunity_id' => $entity->id]);
$files = $entity->getFiles('eventImporter');

$template = '
<li id="file-{{id}}" class="widget-list-item">
    <a href="{{url}}" rel="noopener noreferrer">{{description}}</a> 
    <div class="botoes">
        <a href="'.$url.'?file={{id}}" class="btn btn-primary hltip js-eventImporter-process" data-hltip-classes="hltip-ajuda" title="Clique para processar o arquivo enviado">processar</a>
        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="icon icon-close hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer"></a>
    </div>
</li>';
?>

<div class="widget">
    <h3 class="editando"><?= \MapasCulturais\i::_e('Importação de eventos')?></h3>
    <div>
        <a class="add js-open-editbox hltip" data-target="#csv-events-file" href="#" title="<?= \MapasCulturais\i::_e('Clique aqui para subir o arquivo')?>"> subir arquivo</a>
    </div>
    <div id="csv-events-file" class="js-editbox mc-left" title="<?= \MapasCulturais\i::_e('Importar CSV de eventos')?>" data-submit-label="Enviar">
    <?php $this->ajaxUploader($entity, 'eventImporter', 'append', 'ul.js-eventImporter', $template, '', false, false, false)?>
    </div>
    <ul class="widget-list js-eventImporter js-slimScroll">
        <?php if(is_array($files)): foreach($files as $file): ?>
            <li id="file-<?php echo $file->id ?>" class="widget-list-item<?php if($this->isEditable()) echo \MapasCulturais\i::_e(' is-editable'); ?>" >
                <a href="<?php echo $file->url;?>"><span><?php echo $file->description ? $file->description : $file->name;?></span></a>
                <?php foreach($filesResumo as $resumo){?>
                    <?php if($resumo->name == "resumo-importacao-cnab240-{$file->id}.csv") {?>
                        <a href="<?php echo $resumo->url;?>"><span>resumo-import-<?=$file->id?></span></a>
                    <?php } ?>
                <?php } ?>
                
                <?php if($processed_at = $entity->cnab240_processed_files->{$file->name} ?? null): ?>
                    - processado em <?= $processed_at ?>
                <?php else: ?>
                <div class="botoes">
                    <a href="<?=$url?>?file=<?=$file->id?>" class="btn btn-primary hltip js-eventImporter-process" data-hltip-classes="hltip-ajuda" title="Clique para processar o arquivo enviado">processar</a>
                    <a data-href="<?php echo $file->deleteUrl?>" data-target="#file-<?php echo $file->id ?>" data-configm-message="Remover este arquivo?" class="delete hltip js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo. Só é possível fazer esta ação antes do processamento."></a>
                </div>
                <?php endif; ?>
            
            </li>
        <?php endforeach; endif;?>
    </ul>
</div>
