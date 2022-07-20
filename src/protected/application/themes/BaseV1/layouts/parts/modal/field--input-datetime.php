<?php
$className = mb_strtolower($entity_classname::getEntityTypeLabel());
?>
<?php $this->part("modal/title", ['title' => $definition['label']]); ?>
<?php if ($field == 'registrationTo'): ?>
    <input type='date' data-enable-time="true" name='<?php echo $field ?>' <?php if($definition['required']) echo 'required' ?>>
<?php else: ?>
    <input type='date' name='<?php echo $field ?>' <?php if($definition['required']) echo 'required' ?>>
<?php endif; ?>
