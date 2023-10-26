<?php
$_placeholder = \MapasCulturais\i::esc_attr__("Insira uma descrição curta");
$this->part("modal/title", ['title' => $definition['label']]);
?>
<textarea name='<?php echo $field ?>' placeholder='<?php echo $_placeholder;?>'
          required style='width: 100%'><?php //echo $entity->$field ?></textarea>