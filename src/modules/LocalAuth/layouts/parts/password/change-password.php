<?php
/** @var MapasCulturais\Theme $this */
use MapasCulturais\App;
use MapasCulturais\i;
$app = App::i();

$this->import('
    change-password
');
?>

<?php if($this->controller->action == 'my-account'): ?>
    <change-password :entity="entity" my-account></change-password>
<?php else :?>
    <change-password :entity="entity"></change-password>
<?php endif; ?>
