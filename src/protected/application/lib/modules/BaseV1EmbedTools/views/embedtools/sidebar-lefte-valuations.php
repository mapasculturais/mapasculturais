<?php
$action = 'single';

$_params = [
    'entity' => $entity,
    'action' => $action,
    'opportunity' => $entity->opportunity
];
?>
<?php $this->part('singles/registration--sidebar--left', $_params); ?>