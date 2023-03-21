<?php
$gif = $this->asset("img/spinner_192.gif", false);
$text = \MapasCulturais\i::esc_attr__('Enviando...');
?>
<hr />
<div class="modal-loading hidden textcenter">
    <p> <?php echo $text; ?> </p> <img src="<?php echo $gif; ?>" alt="<?php echo $text; ?>">
</div>

<?php $app->applyHook('mapasculturais.add_entity_modal.form:before'); ?>