<?php 

use MapasCulturais\i;
$app = MapasCulturais\App::i();
$url = $app->createUrl('eventimporter','processFile');
$files = $entity->getFiles('event-import-file');
$url_file_example =  $app->createUrl('eventimporter','downloadExample');
$processed_file_meta = json_decode(json_encode($app->user->profile->event_importer_processed_file), true) ?? [];

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
        <a class="download" href="<?= $url_file_example?>?type=csv" style="margin-right: 10px;"><?= i::_e('Baixar modelo CSV')?></a>
        <a class="download" href="<?= $url_file_example?>?type=xls" style="margin-right: 10px;"><?= i::_e('Baixar modelo XLS')?></a>

        <a class="add btn btn-default js-open-editbox hltip" data-target="#csv-events-file" href="#"> <?= i::_e('Enviar arquivo para importação')?></a>
    </div>
    <div id="csv-events-file" class="js-editbox mc-left" title="<?= i::_e('Importar CSV de eventos')?>" data-submit-label="Enviar">
        <?php $this->ajaxUploader($entity, 'event-import-file', 'append', '.js-eventImporter', $template, '', false, false, false)?>
    </div>
    <div class="js-eventImporter">
        <?php if(is_array($files)):?>
            <?php foreach($files as $file): ?>
                <article id="file-<?php echo $file->id ?>" class="objeto <?php if($this->isEditable()) echo i::_e(' is-editable'); ?>" >
                    <h1><a href="<?php echo $file->url;?>" download><?php echo $file->description ? $file->description : $file->name;?></a></h1>
                    <div class="objeto-meta">
                        <?php if(in_array($file->name, array_keys($processed_file_meta))): ?>                        
                        <div><span class="label"><?= i::_e('Arquivo:')?> </span> <?=$file->name?></div>
                        <div><span class="label"><?= i::_e('Data de envio:')?> </span> <?=$file->createTimestamp->format("d/m/Y H:i")?></div>
                        <div><span class="label"><?= i::_e('processado em:')?> </span> <?=$processed_file_meta[$file->name]['date']?></div>
                        <div><span class="label"><?= i::_e('Quantidade processada:')?> </span> <?=$processed_file_meta[$file->name]['countProsess']?></div> 
                        <div><span class="label"><?= i::_e('Tipo:')?> </span> <?=in_array("typeFile", array_keys($processed_file_meta[$file->name])) ? $processed_file_meta[$file->name]['typeFile'] : i::_e('Não definido')?></div><br> <br>

                        <span class="label"><?= i::_e('ID dos Eventos:')?></span> <br>
                        <?php foreach($processed_file_meta[$file->name]['eventsIdList'] as $id):?>
                            <?php $event_url = $app->createUrl("evento/{$id}")?>
                            <div><a href="<?=$event_url?>" target="_blank"><?=$id?></a></div>
                        <?php endforeach;?>
                        <?php endif; ?>
                    </div>
                    <div class="entity-actions">
                        <?php if(!in_array($file->name, array_keys($processed_file_meta))): ?>
                        <a href="<?=$url?>?file=<?=$file->id?>" class="btn btn-small btn-primary js-validador-process"><?= i::_e('Processar')?></a>
                        <a data-href="<?php echo $file->deleteUrl?>" data-target="#file-<?php echo $file->id ?>" data-configm-message="Remover este arquivo?" class="btn btn-small btn-danger js-remove-item"><?= i::_e('Excluir')?></a>
                        <?php endif; ?>
                    </div>
                
                </article>
            <?php endforeach;?>
        <?php endif;?>

    </div>
</div>
