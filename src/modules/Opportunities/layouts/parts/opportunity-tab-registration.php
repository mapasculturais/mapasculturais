<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    opportunity-phase-header
    opportunity-registrations-table
    mc-tab
');
?>
<mc-tab label="<?= i::__('Inscritos') ?>" slug="registrations">
    <div class="opportunity-registrations__container">
        <opportunity-phase-header :phase="entity"></opportunity-phase-header>

        <opportunity-registrations-table :phase="entity"></opportunity-registrations-table>
    </div>
</mc-tab>
