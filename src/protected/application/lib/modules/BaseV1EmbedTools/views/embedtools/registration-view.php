<?php
$action = preg_replace("#^(\w+/)#", "", $this->template);
$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];
?>
<?php $this->part('singles/registration-single--fields', $_params) ?>