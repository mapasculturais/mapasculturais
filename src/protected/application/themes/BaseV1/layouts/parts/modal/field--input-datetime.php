<?php
$className = mb_strtolower($entity_classname::getEntityTypeLabel());
?>
<?php $this->part("modal/title", ['title' => $definition['label']]); ?>
<input type='date' name='<?php echo $field ?>' value="<?php //echo $entity->$field ?>" <?php if($definition['required']) echo 'required' ?>>