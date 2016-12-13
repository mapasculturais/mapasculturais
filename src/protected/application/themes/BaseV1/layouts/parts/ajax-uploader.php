<?php if($this->controller->action === 'create'): ?>
    <span class='js-dialog-disabled' data-message="<?php \MapasCulturais\i::esc_attr_e('Para subir arquivos você primeiro deve salvar.'); ?>" ></span>
<?php else: ?>

<form class="js-ajax-upload" style="display:none"
      data-action="<?php echo $response_action ?>"
      data-target="<?php echo $response_target ?>"
      data-group="<?php echo $file_group ?>"
      <?php if($response_transform) echo " data-transform=\"$response_transform\" " ?>
      method="post"
      action="<?php echo $this->controller->createUrl('upload', array('id' => $file_owner->id)) ?>"
      enctype="multipart/form-data">
    <div class="alert danger hidden"></div>
    <?php if($response_template): ?><script type="js-template"><?php echo $response_template; ?></script><?php endif; ?>
    <?php if($add_description): ?> <input type="text" name="description[<?php echo $file_group ?>]" placeholder="<?php \MapasCulturais\i::esc_attr_e("Título");?>" /><br /><?php endif; ?>

    <?php if($file_types): ?><p class="form-help"><?php \MapasCulturais\i::_e("Tipos de arquivos suportados: ");?><?php echo $file_types; ?></p><?php endif; ?>
    <p class="form-help"><?php \MapasCulturais\i::_e("Tamanho máximo do arquivo: ");?><?php echo $app->maxUploadSize; ?></p>
    <input type="file" name="<?php echo $file_group ?>" />

</form>

<div class="js-ajax-upload-progress">
    <div class="progress inactive">
        <div class="bar"></div >
        <div class="percent">0%</div >
    </div>
</div>
<?php endif; ?>
