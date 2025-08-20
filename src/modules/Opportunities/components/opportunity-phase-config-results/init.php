<?php 

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
*/

$seals = $app->user->getHasControlSeals();

$this->jsObject['config']['opportunityPhaseConfigResults'] = [
    'seals' => $seals,
];