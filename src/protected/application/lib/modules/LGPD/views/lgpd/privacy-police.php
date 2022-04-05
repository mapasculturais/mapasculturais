<?php

use MapasCulturais\App;

$app = App::i();
$config = $app->config['module.LGPD'];

$url = $app->createUrl('lgpd','aceptPolicePrivacy');
$this->part('lgpd/acept-policePrivacy', ['url' => $url, 'config' => $config]);

