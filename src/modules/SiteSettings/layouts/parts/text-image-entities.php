<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    oc-entities
');
?>
<div class="text-image-entities">
    <oc-entities :entity="entity" :tabGroups="tab.submenu"></oc-entities>
</div>