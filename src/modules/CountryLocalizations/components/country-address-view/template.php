<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    brasil-address-view
    international-address-view
');
?>

<div class="country-address-view">
    <brasil-address-view v-if="entity.address_level0 == 'BR'" :entity="entity"></brasil-address-view>
    <international-address-view v-else :entity="entity"></international-address-view>
</div>