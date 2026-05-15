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
    <entity-profile :entity="targetEntity" size="small"></entity-profile>
</span>
<div v-if="errorMessages.length" class="field__error">
    {{ errorMessages.join(', ') }}
</div>
