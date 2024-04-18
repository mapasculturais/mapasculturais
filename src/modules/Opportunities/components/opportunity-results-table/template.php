<?php
/**
 * @var MapasCulturais\App $app
 * @var MapasCulturais\Themes\BaseV2\Theme $this
 */

use MapasCulturais\i;

$this->import('
    opportunity-registrations-table
');
?>

<opportunity-registrations-table :phase="lastPhase" :visible-columns="visibleColumns" :avaliable-columns="visibleColumns" hide-filters hide-sort status-not-editable>
    <template #title>
        <b><?= i::__("Clique no número de uma inscrição para conferir todas as avaliações realizadas.") ?></b>
    </template>
</opportunity-registrations-table>