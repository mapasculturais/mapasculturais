<?php
/**
 * @var \MapasCulturais\Themes\BaseV2\Theme $this
 * @var \MapasCulturais\App $app
 * 
 */

use MapasCulturais\i;

$this->import('mc-side-menu')
?>

<div>
    <mc-side-menu :is-open="open" @toggle="toggle"></mc-side-menu>
</div>