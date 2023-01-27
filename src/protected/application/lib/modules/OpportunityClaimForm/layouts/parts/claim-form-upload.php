<?php 

use MapasCulturais\i;
$app = MapasCulturais\App::i();
$url = "";
$files = $entity->getFiles('formClaimUpload');

$template = '
<article id="file-{{id}}" class="objeto">
    <h1><a href="{{url}}" rel="noopener noreferrer">{{description}}</a></h1> 
    <div class="botoes">
        <a href="'.$url.'?file={{id}}" class="btn btn-small btn-primary js-formClaim-process">Processar</a>
        <a data-href="{{deleteUrl}}" data-target="#file-{{id}}" data-configm-message="Remover este arquivo?" class="btn btn-small btn-danger js-remove-item" data-hltip-classes="hltip-ajuda" title="Excluir arquivo" rel="noopener noreferrer">Excluir</a>
    </div>
</article>';
?>
<div id="event-importer">
    <div style="margin:1em 0em; text-align: right;">

        <a class="add btn btn-default js-open-editbox hltip" data-target="#form-claim-file" href="#"> <?= i::_e('Vincular arquivo')?></a>
    </div>
    <div id="form-claim-file" class="js-editbox mc-left" title="<?= i::_e('Importar CSV de eventos')?>" data-submit-label="Enviar">
        <?php $this->ajaxUploader($entity, 'formClaimUpload', 'append', '.js-formClaim', $template, '', false, false, false)?>
    </div>
    <div class="js-formClaim">
        <?php if(is_array($files)):?>
            <?php foreach($files as $file): ?>
                <article id="file-<?php echo $file->id ?>" class="objeto <?php if($this->isEditable()) echo i::_e(' is-editable'); ?>" >
                    <h1><a href="<?php echo $file->url;?>" download><?php echo $file->description ? $file->description : $file->name;?></a></h1>
                
                </article>
            <?php endforeach;?>
        <?php endif;?>

    </div>
</div>
