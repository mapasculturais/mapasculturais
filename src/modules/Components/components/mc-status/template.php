<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-icon
');
?>

<div :class="statusClass">
    <mc-icon name="dot"></mc-icon>
    <span>        
        {{statusName}}
    </span>
</div>