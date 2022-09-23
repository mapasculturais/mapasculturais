<?php
use MapasCulturais\i;
eval(\psy\sh());
?>
<h1><?= i::__('Permissão Negada') ?></h1>
<?php 
if ($app->mode == 'development') {
    \dump($e);
}