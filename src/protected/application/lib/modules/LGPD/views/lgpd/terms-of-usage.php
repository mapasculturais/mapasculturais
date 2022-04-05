<?php

use MapasCulturais\App;

$app = App::i();
$config = $app->config['module.LGPD'];

$url = $app->createUrl('lgpd','aceptTermsOfUsage');
$this->part('lgpd/acept-termsOfUsage', ['url' => $url, 'config' => $config]);

