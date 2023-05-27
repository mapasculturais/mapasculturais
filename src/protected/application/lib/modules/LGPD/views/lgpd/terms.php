<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    accept-terms 
    mc-breadcrumb 
    mc-card
    mc-link
    tabs
');

$this->breadcrumb = [
    ['label' => i::__('Painel'), 'url' => $app->createUrl('panel', 'index')],
    ['label' => i::__('Termos e Condições'), 'url' => $app->createUrl('panel', 'terms')],
];
?>
<mc-breadcrumb></mc-breadcrumb>
<accept-terms></accept-terms>
