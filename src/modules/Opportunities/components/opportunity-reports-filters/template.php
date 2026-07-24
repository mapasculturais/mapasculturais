<?php

/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    mc-select
');
?>
<div class="opportunity-reports-filters">
    <label class="opportunity-reports-filters__label"><?= i::__('Filtrar dados por') ?></label>
    <mc-select class="opportunity-reports-filters__status" :options="statusOptions" :default-value="modelValue.status" placeholder="<?= i::esc_attr__('Status') ?>" @update:default-value="onStatusChange" small></mc-select>
</div>
