<?php 

use MapasCulturais\i;
$app = MapasCulturais\App::i();
$url = $app->createUrl('eventimporter','processFile');
$files = $entity->getFiles('event-import-file');
$url_file_example =  $app->createUrl('eventimporter','downloadExample');
$template = '
<article id="file-{{id}}" class="objeto">
    <h1><a href="{{url}}" rel="noopener noreferrer">{{description}}</a></h1> 
    <div class="botoes">
        <a href="'.$url.'?file={{id}}" class="btn btn-small btn-primary js-eventImporter-process">Processar</a>
        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="btn btn-small btn-danger js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer">Excluir</a>
    </div>
</article>';
?>
<div id="event-importer">
    <div style="margin:1em 0em; text-align: right;">
        <a class="download" href="<?= $url_file_example?>" download><?= i::_e('Baixar modelo')?></a>
        <a class="add btn btn-default js-open-editbox hltip" data-target="#csv-events-file" href="#"> <?= i::_e('Enviar arquivo para importação')?></a>
    </div>
    <div id="csv-events-file" class="js-editbox mc-left" title="<?= i::_e('Importar CSV de eventos')?>" data-submit-label="Enviar">
        <?php $this->ajaxUploader($entity, 'event-import-file', 'append', '.js-eventImporter', $template, '', false, false, false)?>
    </div>
    <div class="js-eventImporter">
        <?php if(is_array($files)): foreach($files as $file): ?>
            <?php $file_process = json_decode($app->user->profile->event_importer_processed_file)?>
            <article id="file-<?php echo $file->id ?>" class="objeto <?php if($this->isEditable()) echo i::_e(' is-editable'); ?>" >
                <h1><a href="<?php echo $file->url;?>" download><?php echo $file->description ? $file->description : $file->name;?></a></h1>
                <div class="objeto-meta">
                    <?php if($processed_at = $file_process->{$file->name} ?? null): ?>                        
                    <div><span class="label"><?= i::_e('Arquivo:')?> </span> <?=$file->name?></div>
                    <div><span class="label"><?= i::_e('Data de envio:')?> </span> <?=$file->createTimestamp->format("d/m/Y H:i")?></div>
                    <div><span class="label"><?= i::_e('processado em:')?> </span> <?=$processed_at?></div>
                    <?php endif; ?>
                </div>
                <div class="entity-actions">
                    <?php if(!$processed_at = $file_process->{$file->name} ?? null): ?>
                    <a href="<?=$url?>?file=<?=$file->id?>" class="btn btn-small btn-primary js-validador-process"><?= i::_e('Processar')?></a>
                    <a data-href="<?php echo $file->deleteUrl?>" data-target="#file-<?php echo $file->id ?>" data-configm-message="Remover este arquivo?" class="btn btn-small btn-danger js-remove-item"><?= i::_e('Excluir')?></a>
                    <?php endif; ?>
                </div>
            
            </article>
        <?php endforeach; endif;?>
    </div>
</div>
