<?php

use MapasCulturais\i;

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

$this->import('
    entity-profile
');

?>
<span class="icon">
    <span v-if="hasErrors()" :class="{'errors' : hasErrors()}">
        <?= i::__('Insira um avatar') ?>
    </span>
    <entity-profile :entity="entity.owner" size="small"></entity-profile>
</span>