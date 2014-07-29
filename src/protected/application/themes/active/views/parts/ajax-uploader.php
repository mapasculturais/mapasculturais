<?php if($this->controller->action === 'create'): ?>
    <span class='js-dialog-disabled' data-message='Para subir arquivos você primeiro deve salvar.'></span>
<?php else: ?>

<form class="js-ajax-upload"
      data-action="<?php echo $response_action ?>"
      data-target="<?php echo $response_target ?>"
      data-group="<?php echo $file_group ?>"
      <?php if($response_transform) echo " data-transform=\"$response_transform\" " ?>
      method="post"
      action="<?php echo $this->controller->createUrl('upload', array('id' => $file_owner->id)) ?>"
      enctype="multipart/form-data">
    <div class="mensagem erro escondido"></div>
    <?php if($response_template): ?><script type="js-template"><?php echo $response_template; ?></script><?php endif; ?>
    <?php if($add_description): ?> <label> Descrição: <input type="text" name="description[<?php echo $file_group ?>]" /> </label><br /><?php endif; ?>
    
    <?php if($file_types): ?><p>Tipos de arquivos suportados: <?php echo $file_types; ?></p><?php endif; ?>
    <p class="form-help">Tamanho máximo do arquivo: <?php echo $app->maxUploadSize; ?></p>
    <input type="file" name="<?php echo $file_group ?>" />
    
</form>
<style>
    .progress { position:relative; max-width:400px; width:100%; border: 1px solid #ddd; padding: 1px; border-radius: 3px; }
    .bar { background-color: #B4F5B4; height:20px; border-radius: 3px; }
    .percent { position:absolute; display:inline-block; top:0px; left:48%; }
</style>
<div class="js-ajax-upload-progress">
    <div class="progress">
        <div class="bar"></div >
        <div class="percent">0%</div >
    </div>
</div>
<?php endif; ?>