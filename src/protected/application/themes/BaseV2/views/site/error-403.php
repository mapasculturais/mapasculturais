<?php
use MapasCulturais\i;
?>
<h1><?= i::__('Permissão Negada') ?></h1>
<?php 
if ($app->mode == 'development') {
    \dump($e);
}