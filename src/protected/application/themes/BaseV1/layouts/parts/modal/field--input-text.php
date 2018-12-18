<?php
$className = mb_strtolower($new_entity->getEntityTypeLabel());
?>
<?php $this->part("modal/title", ['title' => $definition['label']]); ?>
<input type='text' name='<?php echo $field ?>' value="<?php echo $new_entity->$field ?>" <?php if($definition['required']) echo 'required' ?>>