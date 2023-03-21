<?php

use MapasCulturais\i;

$this->import('accept-terms mapas-breadcrumb mapas-card  tabs mc-link');

$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Termos e Condições'), 'url' => $app->createUrl('panel', 'terms')],
];
?>
<mapas-breadcrumb></mapas-breadcrumb>
<accept-terms></accept-terms>
