<?php $this->part("modal/title", ['title' => $definition['label']]); ?>
<textarea name='<?php echo $field ?>' required style='width: 100%'><?php echo $new_entity->$field ?></textarea>